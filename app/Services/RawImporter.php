<?php

namespace App\Services;

use App\Models\Universe;
use App\Models\Timeline;
use App\Models\Character;
use App\Models\Scene;
use App\Models\Tag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RawImporter
{
    private string $filePath;
    private array $importLog = [];
    private array $stats = [
        'scenes_imported' => 0,
        'scenes_skipped' => 0,
        'characters_created' => 0,
        'timelines_created' => 0,
        'tags_created' => 0,
        'errors' => [],
    ];

    private Universe $universe;
    private array $timelines = [];
    private array $characters = [];
    private array $tags = [];

    // Scene boundary markers
    private const EMOJI_MARKERS = ['🚂', '📍', '💬', '🎬', '💭', '🏠', '☕', '🌙', '🌅'];
    private const TIME_PATTERN = '/^\d{2}\.\d{2}\s*—/';
    private const SCENE_PATTERN = '/^Scene\s+\d+:/i';
    private const NUMBERED_SECTION = '/^\d+\.\s+".+"/'; // e.g., 4. "Title"
    private const DATE_PATTERN = '/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+\d+$/'; // e.g., Feb 2
    private const ACT_PATTERN = '/^(ACT|PART|CHAPTER)\s+/i';
    private const TIMELINE_HEADER = '/^(MALAM|PAGI|SIANG|SORE)\s+(SEBELUMNYA|HARI INI|KEMARIN)/i';

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Main import method
     */
    public function import(int $userId, bool $dryRun = false): array
    {
        if (!file_exists($this->filePath)) {
            throw new \Exception("File not found: {$this->filePath}");
        }

        $this->log("Starting import from {$this->filePath}");
        $this->log("Mode: " . ($dryRun ? "DRY RUN" : "LIVE"));

        DB::beginTransaction();

        try {
            // Step 1: Create universe
            $this->universe = $this->createUniverse($userId);
            $this->log("Universe created: {$this->universe->name} (ID: {$this->universe->id})");

            // Step 2: Create timelines
            $this->createTimelines();
            $this->log("Timelines created: " . count($this->timelines));

            // Step 3: Create base characters
            $this->createCharacters();
            $this->log("Characters created: " . count($this->characters));

            // Step 4: Create tags
            $this->createTags();
            $this->log("Tags created: " . count($this->tags));

            // Step 5: Parse and import scenes
            $this->parseAndImportScenes();
            $this->log("Scenes imported: {$this->stats['scenes_imported']}");

            if ($dryRun) {
                DB::rollBack();
                $this->log("DRY RUN - All changes rolled back");
            } else {
                DB::commit();
                $this->log("Import completed successfully");
            }

            return [
                'success' => true,
                'stats' => $this->stats,
                'log' => $this->importLog,
                'universe_id' => $this->universe->id,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            $this->log("ERROR: " . $e->getMessage());
            $this->stats['errors'][] = $e->getMessage();

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'stats' => $this->stats,
                'log' => $this->importLog,
            ];
        }
    }

    /**
     * Create the main universe
     */
    private function createUniverse(int $userId): Universe
    {
        return Universe::create([
            'user_id' => $userId,
            'name' => 'INFJ × INFP Journey',
            'description' => 'A deep exploration of INFJ and INFP relationship dynamics, including multiple perspectives and character studies.',
            'is_public' => false,
            'allow_fork' => true,
        ]);
    }

    /**
     * Create all timelines
     */
    private function createTimelines(): void
    {
        $timelineData = [
            'primary' => [
                'name' => 'Primary Story',
                'description' => 'Main INFJ × INFP relationship narrative',
                'is_canon' => true,
                'color' => '#1CB0F6',
            ],
            'mbti' => [
                'name' => 'MBTI Perspectives',
                'description' => '16 personality types analyzing the relationship',
                'is_canon' => false,
                'color' => '#8B5CF6',
            ],
            'rania_papa' => [
                'name' => 'Rania & Papa',
                'description' => 'Father-daughter relationship scenes',
                'is_canon' => false,
                'color' => '#10B981',
            ],
            'rania_mama_original' => [
                'name' => 'Rania & Mama - Original',
                'description' => 'Original mother-daughter dynamics',
                'is_canon' => false,
                'color' => '#F59E0B',
            ],
            'rania_mama_healthy' => [
                'name' => 'Rania & Mama - Healthy',
                'description' => 'Alternative healthy relationship scenarios',
                'is_canon' => false,
                'color' => '#58CC02',
            ],
        ];

        foreach ($timelineData as $key => $data) {
            $this->timelines[$key] = Timeline::create([
                'universe_id' => $this->universe->id,
                ...$data,
            ]);
            $this->stats['timelines_created']++;
        }
    }

    /**
     * Create base characters
     */
    private function createCharacters(): void
    {
        $characterData = [
            'infj' => [
                'name' => 'INFJ',
                'nickname' => 'The Advocate',
                'type' => 'INFJ',
                'description' => 'Female protagonist - thoughtful, planning-oriented, caring',
                'traits' => json_encode(['planning', 'perceptive', 'caring', 'structured']),
                'color' => '#1CB0F6',
            ],
            'infp' => [
                'name' => 'INFP',
                'nickname' => 'The Mediator',
                'type' => 'INFP',
                'description' => 'Male protagonist - spontaneous, idealistic, go-with-the-flow',
                'traits' => json_encode(['spontaneous', 'idealistic', 'flexible', 'deep']),
                'color' => '#8B5CF6',
            ],
            'rania' => [
                'name' => 'Rania',
                'type' => 'INFJ',
                'description' => 'Young INFJ daughter navigating family dynamics',
                'traits' => json_encode(['sensitive', 'quiet', 'thoughtful']),
                'color' => '#EC4899',
            ],
            'papa' => [
                'name' => 'Papa',
                'description' => 'Understanding and supportive father figure',
                'traits' => json_encode(['understanding', 'patient', 'supportive']),
                'color' => '#10B981',
            ],
            'mama' => [
                'name' => 'Mama',
                'description' => 'Mother with different communication style',
                'traits' => json_encode(['direct', 'practical', 'caring']),
                'color' => '#F59E0B',
            ],
        ];

        foreach ($characterData as $key => $data) {
            $this->characters[$key] = Character::create([
                'universe_id' => $this->universe->id,
                ...$data,
            ]);
            $this->stats['characters_created']++;
        }

        // Create MBTI analyst characters (#1-#16)
        $mbtiTypes = [
            'INFJ', 'ENFJ', 'ENFP', 'ISFP', 'INTP', 'INTJ', 'INFP', 'ISFJ',
            'ENTP', 'ESFP', 'ESFJ', 'ISTP', 'ENTJ', 'ESTP', 'ISTJ', 'ESTJ'
        ];

        foreach ($mbtiTypes as $index => $type) {
            $number = $index + 1;
            $this->characters["analyst_{$number}"] = Character::create([
                'universe_id' => $this->universe->id,
                'name' => "#{$number} — {$type}",
                'type' => $type,
                'description' => "{$type} perspective analyst",
                'color' => $this->generateColor($index),
            ]);
            $this->stats['characters_created']++;
        }
    }

    /**
     * Create base tags
     */
    private function createTags(): void
    {
        $tagData = [
            ['name' => 'Train Journey', 'category' => 'event', 'color' => '#1CB0F6'],
            ['name' => 'Chat Conversation', 'category' => 'event', 'color' => '#8B5CF6'],
            ['name' => 'Inner Monologue', 'category' => 'narrative', 'color' => '#EC4899'],
            ['name' => 'MBTI Analysis', 'category' => 'theme', 'color' => '#F59E0B'],
            ['name' => 'Family Dynamics', 'category' => 'theme', 'color' => '#10B981'],
            ['name' => 'Healthy Alternative', 'category' => 'branch', 'color' => '#58CC02'],
            ['name' => 'Planning', 'category' => 'emotion', 'color' => '#1CB0F6'],
            ['name' => 'Spontaneous', 'category' => 'emotion', 'color' => '#8B5CF6'],
            ['name' => 'Romantic', 'category' => 'emotion', 'color' => '#EC4899'],
            ['name' => 'Conflict', 'category' => 'emotion', 'color' => '#EF4444'],
            ['name' => 'Resolution', 'category' => 'emotion', 'color' => '#58CC02'],
        ];

        foreach ($tagData as $data) {
            $tag = Tag::create([
                'universe_id' => $this->universe->id,
                ...$data,
            ]);
            $this->tags[$data['name']] = $tag;
            $this->stats['tags_created']++;
        }
    }

    /**
     * Parse raw.md and import all scenes
     */
    private function parseAndImportScenes(): void
    {
        $this->log("Parsing {$this->filePath}...");

        $handle = fopen($this->filePath, 'r');
        if (!$handle) {
            throw new \Exception("Cannot open file: {$this->filePath}");
        }

        $currentScene = null;
        $sceneContent = [];
        $lineNumber = 0;
        $sceneOrder = [
            'primary' => 0,
            'mbti' => 0,
            'rania_papa' => 0,
            'rania_mama_original' => 0,
            'rania_mama_healthy' => 0,
        ];

        while (($line = fgets($handle)) !== false) {
            $lineNumber++;

            // Check if this line starts a new scene
            if ($this->isSceneBoundary($line)) {
                // Save previous scene if exists
                if ($currentScene && !empty($sceneContent)) {
                    $this->saveScene($currentScene, $sceneContent, $sceneOrder);
                }

                // Start new scene
                $currentScene = [
                    'start_line' => $lineNumber,
                    'title' => $this->extractSceneTitle($line),
                    'timeline_key' => 'primary', // Default
                    'characters' => [],
                    'tags' => [],
                ];
                $sceneContent = [$line];
            } elseif ($currentScene !== null) {
                $sceneContent[] = $line;

                // Detect timeline based on content
                $this->detectTimeline($currentScene, $line, $lineNumber);

                // Detect characters
                $this->detectCharacters($currentScene, $line);

                // Detect tags
                $this->detectTags($currentScene, $line);
            }

            // Progress indicator every 10000 lines
            if ($lineNumber % 10000 === 0) {
                $this->log("Processed {$lineNumber} lines...");
            }
        }

        // Save last scene
        if ($currentScene && !empty($sceneContent)) {
            $this->saveScene($currentScene, $sceneContent, $sceneOrder);
        }

        fclose($handle);
        $this->log("Parsing complete. Total lines: {$lineNumber}");
    }

    /**
     * Check if a line is a scene boundary
     */
    private function isSceneBoundary(string $line): bool
    {
        $line = trim($line);

        // Skip empty lines
        if (empty($line)) {
            return false;
        }

        // Check for emoji markers
        foreach (self::EMOJI_MARKERS as $emoji) {
            if (str_starts_with($line, $emoji)) {
                return true;
            }
        }

        // Check for timestamp pattern (HH.MM — )
        if (preg_match(self::TIME_PATTERN, $line)) {
            return true;
        }

        // Check for "Scene X:" pattern
        if (preg_match(self::SCENE_PATTERN, $line)) {
            return true;
        }

        // Check for numbered sections: 4. "Title"
        if (preg_match(self::NUMBERED_SECTION, $line)) {
            return true;
        }

        // Check for date markers: Feb 2
        if (preg_match(self::DATE_PATTERN, $line)) {
            return true;
        }

        // Check for ACT/PART/CHAPTER markers
        if (preg_match(self::ACT_PATTERN, $line)) {
            return true;
        }

        // Check for Indonesian timeline headers
        if (preg_match(self::TIMELINE_HEADER, $line)) {
            return true;
        }

        return false;
    }

    /**
     * Extract scene title from first line
     */
    private function extractSceneTitle(string $line): string
    {
        $line = trim($line);

        // Remove emoji markers
        foreach (self::EMOJI_MARKERS as $emoji) {
            $line = str_replace($emoji, '', $line);
        }

        $line = trim($line);

        // If empty after removing emoji, use a default
        if (empty($line)) {
            return 'Scene ' . ($this->stats['scenes_imported'] + 1);
        }

        // Limit length
        return Str::limit($line, 100);
    }

    /**
     * Detect which timeline this scene belongs to
     */
    private function detectTimeline(array &$sceneData, string $line, int $lineNumber): void
    {
        // MBTI Perspectives (around lines 27800-28000)
        if ($lineNumber >= 27800 && $lineNumber <= 28000) {
            $sceneData['timeline_key'] = 'mbti';
            return;
        }

        // Check content for specific patterns
        $lowerLine = mb_strtolower($line);

        if (str_contains($lowerLine, 'rania') && str_contains($lowerLine, 'papa')) {
            $sceneData['timeline_key'] = 'rania_papa';
        } elseif (str_contains($lowerLine, 'rania') && str_contains($lowerLine, 'mama')) {
            if (str_contains($lowerLine, 'healthy version') || str_contains($lowerLine, 'healthy)')) {
                $sceneData['timeline_key'] = 'rania_mama_healthy';
            } else {
                $sceneData['timeline_key'] = 'rania_mama_original';
            }
        }
    }

    /**
     * Detect characters in the scene
     */
    private function detectCharacters(array &$sceneData, string $line): void
    {
        // Check for main characters
        if (preg_match('/^INFJ:/i', $line) || str_contains($line, 'Inner monologue INFJ:')) {
            $sceneData['characters']['infj'] = true;
        }

        if (preg_match('/^INFP:/i', $line) || str_contains($line, 'Inner monologue INFP:')) {
            $sceneData['characters']['infp'] = true;
        }

        // Check for family characters
        $lowerLine = mb_strtolower($line);
        if (str_contains($lowerLine, 'rania')) {
            $sceneData['characters']['rania'] = true;
        }
        if (str_contains($lowerLine, 'papa:') || str_contains($lowerLine, 'ayah')) {
            $sceneData['characters']['papa'] = true;
        }
        if (str_contains($lowerLine, 'mama:') || str_contains($lowerLine, 'ibu')) {
            $sceneData['characters']['mama'] = true;
        }

        // Check for MBTI analysts
        if (preg_match('/#(\d+)\s*—\s*([A-Z]{4})/', $line, $matches)) {
            $number = (int)$matches[1];
            if ($number >= 1 && $number <= 16) {
                $sceneData['characters']["analyst_{$number}"] = true;
            }
        }
    }

    /**
     * Detect tags based on content
     */
    private function detectTags(array &$sceneData, string $line): void
    {
        $lowerLine = mb_strtolower($line);

        if (str_contains($lowerLine, '🚂') || str_contains($lowerLine, 'kereta') || str_contains($lowerLine, 'stasiun')) {
            $sceneData['tags']['Train Journey'] = true;
        }

        if (preg_match('/^(INFJ|INFP):/i', $line)) {
            $sceneData['tags']['Chat Conversation'] = true;
        }

        if (str_contains($line, 'Inner monologue')) {
            $sceneData['tags']['Inner Monologue'] = true;
        }

        if (preg_match('/#\d+\s*—\s*[A-Z]{4}/', $line)) {
            $sceneData['tags']['MBTI Analysis'] = true;
        }

        if (str_contains($lowerLine, 'rania') || str_contains($lowerLine, 'papa') || str_contains($lowerLine, 'mama')) {
            $sceneData['tags']['Family Dynamics'] = true;
        }

        if (str_contains($lowerLine, 'healthy version')) {
            $sceneData['tags']['Healthy Alternative'] = true;
        }
    }

    /**
     * Save a scene to the database
     */
    private function saveScene(array $sceneData, array $contentLines, array &$sceneOrder): void
    {
        try {
            $timelineKey = $sceneData['timeline_key'];
            $sceneOrder[$timelineKey]++;

            // Join content lines
            $content = implode('', $contentLines);
            $content = trim($content);

            // Skip if content is too short
            if (mb_strlen($content) < 50) {
                $this->stats['scenes_skipped']++;
                $this->log("Skipped short scene at line {$sceneData['start_line']}");
                return;
            }

            // Create scene
            $scene = Scene::create([
                'timeline_id' => $this->timelines[$timelineKey]->id,
                'title' => $sceneData['title'],
                'content' => $content,
                'order' => $sceneOrder[$timelineKey],
                'word_count' => str_word_count($content),
            ]);

            // Attach characters
            foreach (array_keys($sceneData['characters']) as $charKey) {
                if (isset($this->characters[$charKey])) {
                    $scene->characters()->attach($this->characters[$charKey]->id);
                }
            }

            // Attach tags
            foreach (array_keys($sceneData['tags']) as $tagName) {
                if (isset($this->tags[$tagName])) {
                    $scene->tags()->attach($this->tags[$tagName]->id);
                }
            }

            $this->stats['scenes_imported']++;

            if ($this->stats['scenes_imported'] % 100 === 0) {
                $this->log("Imported {$this->stats['scenes_imported']} scenes...");
            }

        } catch (\Exception $e) {
            $this->stats['scenes_skipped']++;
            $this->stats['errors'][] = "Scene at line {$sceneData['start_line']}: " . $e->getMessage();
            Log::error("Scene import error", [
                'line' => $sceneData['start_line'],
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Generate a color for character
     */
    private function generateColor(int $index): string
    {
        $colors = [
            '#1CB0F6', '#8B5CF6', '#EC4899', '#F59E0B', '#10B981', '#58CC02',
            '#EF4444', '#3B82F6', '#6366F1', '#8B5CF6', '#A855F7', '#D946EF',
            '#EC4899', '#F43F5E', '#F97316', '#EAB308'
        ];
        return $colors[$index % count($colors)];
    }

    /**
     * Add entry to import log
     */
    private function log(string $message): void
    {
        $timestamp = now()->format('Y-m-d H:i:s');
        $this->importLog[] = "[{$timestamp}] {$message}";
        Log::info("RawImporter: {$message}");
    }

    /**
     * Get import statistics
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * Get import log
     */
    public function getLog(): array
    {
        return $this->importLog;
    }
}
