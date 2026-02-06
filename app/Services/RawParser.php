<?php

namespace App\Services;

class RawParser
{
    /**
     * Scene title patterns - lines that start scenes.
     */
    protected array $sceneTitlePatterns = [
        '/^🚂\s*(.+)$/u',     // Train emoji scenes
        '/^🎬\s*(.+)$/u',     // Movie clapper scenes
        '/^🌙\s*(.+)$/u',     // Night scenes
        '/^☀️\s*(.+)$/u',     // Day scenes
        '/^🌅\s*(.+)$/u',     // Sunset scenes
        '/^🏠\s*(.+)$/u',     // Home scenes
        '/^💭\s*(.+)$/u',     // Thought scenes
    ];

    /**
     * Patterns to detect and skip (prompts, meta, interruptions).
     */
    protected array $skipPatterns = [
        '/^Claude\'s response was interrupted/i',
        '/^Bagaimana tipikal/i',
        '/^Coba skenarionya/i',
        '/^Minute by minute/i',
        '/^Oke,? ini dia/i',
        '/^Oke oke, aku adjust/i',
        '/^Let me rewrite/i',
    ];

    /**
     * Date patterns for story dates.
     */
    protected array $datePatterns = [
        '/^(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s+(\d{1,2})$/i',
    ];

    /**
     * Timestamp patterns within content.
     */
    protected array $timestampPatterns = [
        '/^(\d{1,2})\.(\d{2})\s*—/u',     // 21.47 —
        '/^(\d{1,2}):(\d{2})\s*—/u',      // 21:47 —
        '/^(\d{1,2})\.(\d{2})$/u',        // 21.47
    ];

    /**
     * Scene header patterns (time of day markers).
     */
    protected array $headerPatterns = [
        '/^MALAM\s+SEBELUMNYA/iu',
        '/^PAGI\s+HARI/iu',
        '/^SIANG\s+HARI/iu',
        '/^SORE\s+(—\s+)?(.+)?/iu',
        '/^MALAM\s+(—\s+)?(.+)?/iu',
    ];

    /**
     * Character dialogue patterns.
     */
    protected array $dialoguePatterns = [
        '/^INFJ:\s*(.*)$/iu',
        '/^INFP:\s*(.*)$/iu',
    ];

    /**
     * Inner monologue patterns.
     */
    protected array $monologuePatterns = [
        '/^Inner monologue\s+(INFJ|INFP):\s*(.*)$/iu',
        '/^Inner monologue:\s*(.*)$/iu',
    ];

    /**
     * Mood keywords for detection.
     */
    protected array $moodKeywords = [
        'romantic' => ['romantis', 'sweet', 'manis', 'cinta', 'love', 'sayang', 'peluk', 'genggam', 'kiss'],
        'happy' => ['senang', 'happy', 'gembira', 'tertawa', 'senyum', 'excited', 'seru'],
        'sad' => ['sedih', 'sad', 'nangis', 'tears', 'crying', 'pisah', 'pergi', 'goodbye', 'bye'],
        'tense' => ['tegang', 'nervous', 'deg-degan', 'cemas', 'worried', 'khawatir'],
        'peaceful' => ['tenang', 'damai', 'calm', 'peaceful', 'nyaman', 'santai'],
        'mysterious' => ['misterius', 'aneh', 'strange', 'weird', 'bingung'],
    ];

    /**
     * Parse raw text into structured scenes.
     */
    public function parse(string $content): array
    {
        $lines = explode("\n", $content);
        $scenes = [];
        $currentScene = null;
        $currentContent = [];
        $skipUntilNextScene = true; // Skip initial prompts/meta

        foreach ($lines as $lineNum => $line) {
            $line = trim($line);

            // Skip empty lines at start of scene
            if (empty($line) && $currentScene === null) {
                continue;
            }

            // Check for skip patterns (prompts, interruptions)
            if ($this->matchesSkipPattern($line)) {
                continue;
            }

            // Check for date pattern
            if ($this->matchesDatePattern($line)) {
                // Date markers usually precede scenes, don't create new scene
                continue;
            }

            // Check for scene title
            $sceneTitle = $this->extractSceneTitle($line);
            if ($sceneTitle !== null) {
                // Save previous scene if exists
                if ($currentScene !== null && !empty($currentContent)) {
                    $currentScene['content'] = $this->processContent($currentContent);
                    $currentScene['characters'] = $this->extractCharacters($currentContent);
                    $currentScene['mood'] = $this->detectMood($currentContent);
                    $scenes[] = $currentScene;
                }

                // Start new scene
                $currentScene = [
                    'title' => $sceneTitle,
                    'content' => '',
                    'summary' => '',
                    'location' => $this->extractLocation($sceneTitle),
                    'date' => null,
                    'mood' => null,
                    'is_branch_point' => false,
                    'characters' => [],
                    'tags' => [],
                ];
                $currentContent = [];
                $skipUntilNextScene = false;
                continue;
            }

            // If we have an active scene, collect content
            if ($currentScene !== null && !$skipUntilNextScene) {
                $currentContent[] = $line;
            }
        }

        // Don't forget the last scene
        if ($currentScene !== null && !empty($currentContent)) {
            $currentScene['content'] = $this->processContent($currentContent);
            $currentScene['characters'] = $this->extractCharacters($currentContent);
            $currentScene['mood'] = $this->detectMood($currentContent);
            $scenes[] = $currentScene;
        }

        // Post-process scenes
        return $this->postProcess($scenes);
    }

    /**
     * Check if line matches any skip pattern.
     */
    protected function matchesSkipPattern(string $line): bool
    {
        foreach ($this->skipPatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if line matches date pattern.
     */
    protected function matchesDatePattern(string $line): bool
    {
        foreach ($this->datePatterns as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Extract scene title from line.
     */
    protected function extractSceneTitle(string $line): ?string
    {
        foreach ($this->sceneTitlePatterns as $pattern) {
            if (preg_match($pattern, $line, $matches)) {
                return trim($matches[1] ?? $line);
            }
        }
        return null;
    }

    /**
     * Extract location from scene title.
     */
    protected function extractLocation(string $title): ?string
    {
        // Common location patterns in titles
        $locationPatterns = [
            '/Bandung/i' => 'Bandung',
            '/Garut/i' => 'Garut',
            '/Jakarta/i' => 'Jakarta',
            '/Stasiun/i' => 'Train Station',
            '/Kereta/i' => 'Train',
            '/Cafe/i' => 'Cafe',
            '/Rumah/i' => 'Home',
            '/Kos(an)?/i' => 'Boarding House',
        ];

        foreach ($locationPatterns as $pattern => $location) {
            if (preg_match($pattern, $title)) {
                return $location;
            }
        }

        // Try to extract from "→" format (e.g., "Bandung → Garut")
        if (preg_match('/(.+?)\s*→\s*(.+?)(?::|$)/u', $title, $matches)) {
            return trim($matches[1]) . ' → ' . trim($matches[2]);
        }

        return null;
    }

    /**
     * Process raw content lines into formatted content.
     */
    protected function processContent(array $lines): string
    {
        $processed = [];
        $inDialogue = false;

        foreach ($lines as $line) {
            // Skip empty lines at start
            if (empty($line) && empty($processed)) {
                continue;
            }

            // Format dialogue
            if (preg_match('/^(INFJ|INFP):\s*"?(.+)"?$/iu', $line, $matches)) {
                $character = strtoupper($matches[1]);
                $dialogue = trim($matches[2], '"');
                $processed[] = "<p class=\"dialogue\"><strong>{$character}:</strong> \"{$dialogue}\"</p>";
                $inDialogue = true;
                continue;
            }

            // Format inner monologue
            if (preg_match('/^Inner monologue\s*(INFJ|INFP)?:\s*(.*)$/iu', $line, $matches)) {
                $character = !empty($matches[1]) ? strtoupper($matches[1]) : '';
                $monologue = trim($matches[2]);
                if ($character) {
                    $processed[] = "<p class=\"inner-monologue\"><em>({$character}'s thoughts: {$monologue})</em></p>";
                } else {
                    $processed[] = "<p class=\"inner-monologue\"><em>({$monologue})</em></p>";
                }
                continue;
            }

            // Format timestamps
            if (preg_match('/^(\d{1,2})[.:](\d{2})\s*—\s*(.*)$/u', $line, $matches)) {
                $time = $matches[1] . ':' . $matches[2];
                $description = trim($matches[3]);
                $processed[] = "<p class=\"timestamp\"><strong>{$time}</strong> — {$description}</p>";
                continue;
            }

            // Format scene headers
            if (preg_match('/^(MALAM|PAGI|SIANG|SORE)\s+(SEBELUMNYA|HARI)?/iu', $line)) {
                $processed[] = "<h4 class=\"scene-header\">{$line}</h4>";
                continue;
            }

            // Regular paragraph
            if (!empty($line)) {
                $processed[] = "<p>{$line}</p>";
            } else {
                // Preserve empty lines as paragraph breaks
                $processed[] = '';
            }
        }

        return implode("\n", $processed);
    }

    /**
     * Extract characters from content.
     */
    protected function extractCharacters(array $lines): array
    {
        $characters = [];

        foreach ($lines as $line) {
            if (preg_match('/^(INFJ|INFP):/iu', $line, $matches)) {
                $char = strtoupper($matches[1]);
                if (!in_array($char, $characters)) {
                    $characters[] = $char;
                }
            }
            if (preg_match('/Inner monologue\s+(INFJ|INFP)/iu', $line, $matches)) {
                $char = strtoupper($matches[1]);
                if (!in_array($char, $characters)) {
                    $characters[] = $char;
                }
            }
        }

        return $characters;
    }

    /**
     * Detect mood from content.
     */
    protected function detectMood(array $lines): ?string
    {
        $content = strtolower(implode(' ', $lines));
        $moodScores = [];

        foreach ($this->moodKeywords as $mood => $keywords) {
            $score = 0;
            foreach ($keywords as $keyword) {
                $score += substr_count($content, $keyword);
            }
            if ($score > 0) {
                $moodScores[$mood] = $score;
            }
        }

        if (empty($moodScores)) {
            return null;
        }

        // Return mood with highest score
        arsort($moodScores);
        return array_key_first($moodScores);
    }

    /**
     * Post-process scenes (generate summaries, detect branch points, etc.).
     */
    protected function postProcess(array $scenes): array
    {
        foreach ($scenes as $index => &$scene) {
            // Generate summary from first few lines of content
            $scene['summary'] = $this->generateSummary($scene['content']);

            // Set order
            $scene['order'] = $index + 1;

            // Detect potential branch points (scenes with "what if", questions, decisions)
            $scene['is_branch_point'] = $this->detectBranchPoint($scene['title'], $scene['content']);

            // Generate tags from content
            $scene['tags'] = $this->generateTags($scene['title'], $scene['content']);
        }

        return $scenes;
    }

    /**
     * Generate summary from content.
     */
    protected function generateSummary(string $content): string
    {
        // Strip HTML tags
        $text = strip_tags($content);

        // Get first 200 characters
        if (strlen($text) > 200) {
            $summary = substr($text, 0, 200);
            // Try to end at word boundary
            $lastSpace = strrpos($summary, ' ');
            if ($lastSpace !== false) {
                $summary = substr($summary, 0, $lastSpace);
            }
            $summary .= '...';
        } else {
            $summary = $text;
        }

        return trim($summary);
    }

    /**
     * Detect if scene is a potential branch point.
     */
    protected function detectBranchPoint(string $title, string $content): bool
    {
        $branchKeywords = [
            'what if',
            'bagaimana kalau',
            'gimana kalo',
            'alternatif',
            'pilihan',
            'keputusan',
            'decision',
            'choice',
        ];

        $combined = strtolower($title . ' ' . strip_tags($content));

        foreach ($branchKeywords as $keyword) {
            if (str_contains($combined, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate tags from content.
     */
    protected function generateTags(string $title, string $content): array
    {
        $tags = [];
        $combined = strtolower($title . ' ' . strip_tags($content));

        // Location tags
        $locationTags = ['bandung', 'garut', 'jakarta', 'kereta', 'stasiun', 'cafe'];
        foreach ($locationTags as $loc) {
            if (str_contains($combined, $loc)) {
                $tags[] = ucfirst($loc);
            }
        }

        // Theme tags
        $themeTags = [
            'goodbye' => ['pisah', 'pergi', 'goodbye', 'selamat tinggal'],
            'first meeting' => ['pertama', 'first', 'kenalan'],
            'travel' => ['jalan', 'trip', 'perjalanan', 'travel'],
            'morning' => ['pagi', 'morning'],
            'night' => ['malam', 'night'],
            'romantic' => ['romantis', 'love', 'sayang'],
        ];

        foreach ($themeTags as $tag => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($combined, $keyword)) {
                    $tags[] = $tag;
                    break;
                }
            }
        }

        return array_unique($tags);
    }

    /**
     * Get statistics about parsed content.
     */
    public function getStats(string $content): array
    {
        $lines = explode("\n", $content);
        $scenes = $this->parse($content);

        return [
            'total_lines' => count($lines),
            'total_scenes' => count($scenes),
            'characters' => $this->countUniqueCharacters($scenes),
            'moods' => $this->countMoods($scenes),
            'locations' => $this->countLocations($scenes),
            'tags' => $this->countTags($scenes),
            'branch_points' => count(array_filter($scenes, fn($s) => $s['is_branch_point'])),
        ];
    }

    protected function countUniqueCharacters(array $scenes): array
    {
        $characters = [];
        foreach ($scenes as $scene) {
            foreach ($scene['characters'] as $char) {
                $characters[$char] = ($characters[$char] ?? 0) + 1;
            }
        }
        return $characters;
    }

    protected function countMoods(array $scenes): array
    {
        $moods = [];
        foreach ($scenes as $scene) {
            if ($scene['mood']) {
                $moods[$scene['mood']] = ($moods[$scene['mood']] ?? 0) + 1;
            }
        }
        return $moods;
    }

    protected function countLocations(array $scenes): array
    {
        $locations = [];
        foreach ($scenes as $scene) {
            if ($scene['location']) {
                $locations[$scene['location']] = ($locations[$scene['location']] ?? 0) + 1;
            }
        }
        return $locations;
    }

    protected function countTags(array $scenes): array
    {
        $tags = [];
        foreach ($scenes as $scene) {
            foreach ($scene['tags'] as $tag) {
                $tags[$tag] = ($tags[$tag] ?? 0) + 1;
            }
        }
        return $tags;
    }
}
