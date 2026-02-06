<?php

namespace App\Http\Controllers;

use App\Models\Scene;
use App\Models\Timeline;
use App\Models\Universe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * Export universe as JSON (full backup).
     */
    public function json(Universe $universe): JsonResponse
    {
        Gate::authorize('view', $universe);

        $data = $this->getUniverseData($universe);

        $filename = $this->sanitizeFilename($universe->name) . '_backup.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export universe as Markdown.
     */
    public function markdown(Universe $universe): StreamedResponse
    {
        Gate::authorize('view', $universe);

        $filename = $this->sanitizeFilename($universe->name) . '.md';

        return response()->streamDownload(function () use ($universe) {
            echo $this->generateUniverseMarkdown($universe);
        }, $filename, [
            'Content-Type' => 'text/markdown',
        ]);
    }

    /**
     * Export single timeline as Markdown.
     */
    public function timelineMarkdown(Timeline $timeline): StreamedResponse
    {
        Gate::authorize('view', $timeline->universe);

        $filename = $this->sanitizeFilename($timeline->name) . '.md';

        return response()->streamDownload(function () use ($timeline) {
            echo $this->generateTimelineMarkdown($timeline);
        }, $filename, [
            'Content-Type' => 'text/markdown',
        ]);
    }

    /**
     * Export single timeline as JSON.
     */
    public function timelineJson(Timeline $timeline): JsonResponse
    {
        Gate::authorize('view', $timeline->universe);

        $data = $this->getTimelineData($timeline);
        $filename = $this->sanitizeFilename($timeline->name) . '.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Export single scene as Markdown.
     */
    public function sceneMarkdown(Scene $scene): StreamedResponse
    {
        Gate::authorize('view', $scene->timeline->universe);

        $filename = $this->sanitizeFilename($scene->title) . '.md';

        return response()->streamDownload(function () use ($scene) {
            echo $this->generateSceneMarkdown($scene);
        }, $filename, [
            'Content-Type' => 'text/markdown',
        ]);
    }

    /**
     * Export single scene as JSON.
     */
    public function sceneJson(Scene $scene): JsonResponse
    {
        Gate::authorize('view', $scene->timeline->universe);

        $data = $this->getSceneData($scene);
        $filename = $this->sanitizeFilename($scene->title) . '.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get full universe data for JSON export.
     */
    protected function getUniverseData(Universe $universe): array
    {
        $universe->load([
            'timelines.scenes.characters',
            'timelines.scenes.tags',
            'characters',
            'tags',
        ]);

        return [
            'export_version' => '1.0',
            'exported_at' => now()->toIso8601String(),
            'universe' => [
                'name' => $universe->name,
                'description' => $universe->description,
                'is_public' => $universe->is_public,
                'allow_fork' => $universe->allow_fork,
                'created_at' => $universe->created_at->toIso8601String(),
            ],
            'characters' => $universe->characters->map(fn ($c) => [
                'name' => $c->name,
                'description' => $c->description,
                'color' => $c->color,
                'type' => $c->type,
                'traits' => $c->traits,
            ])->toArray(),
            'tags' => $universe->tags->map(fn ($t) => [
                'name' => $t->name,
                'color' => $t->color,
                'category' => $t->category,
            ])->toArray(),
            'timelines' => $universe->timelines->map(fn ($timeline) => $this->getTimelineData($timeline))->toArray(),
        ];
    }

    /**
     * Get timeline data for export.
     */
    protected function getTimelineData(Timeline $timeline): array
    {
        $timeline->load(['scenes.characters', 'scenes.tags']);

        return [
            'name' => $timeline->name,
            'description' => $timeline->description,
            'color' => $timeline->color,
            'is_canon' => $timeline->is_canon,
            'scenes' => $timeline->scenes->sortBy('order')->map(fn ($scene) => $this->getSceneData($scene))->values()->toArray(),
        ];
    }

    /**
     * Get scene data for export.
     */
    protected function getSceneData(Scene $scene): array
    {
        return [
            'title' => $scene->title,
            'content' => $scene->content,
            'summary' => $scene->summary,
            'order' => $scene->order,
            'date' => $scene->date,
            'location' => $scene->location,
            'mood' => $scene->mood,
            'pov' => $scene->pov,
            'is_branch_point' => $scene->is_branch_point,
            'branch_question' => $scene->branch_question,
            'characters' => $scene->characters->pluck('name')->toArray(),
            'tags' => $scene->tags->pluck('name')->toArray(),
        ];
    }

    /**
     * Generate universe Markdown content.
     */
    protected function generateUniverseMarkdown(Universe $universe): string
    {
        $universe->load(['timelines.scenes', 'characters', 'tags']);

        $md = "# {$universe->name}\n\n";

        if ($universe->description) {
            $md .= "{$universe->description}\n\n";
        }

        $md .= "---\n\n";

        // Characters section
        if ($universe->characters->isNotEmpty()) {
            $md .= "## Characters\n\n";
            foreach ($universe->characters as $character) {
                $md .= "### {$character->name}";
                if ($character->type) {
                    $md .= " ({$character->type})";
                }
                $md .= "\n\n";
                if ($character->description) {
                    $md .= "{$character->description}\n\n";
                }
            }
            $md .= "---\n\n";
        }

        // Timelines and scenes
        foreach ($universe->timelines as $timeline) {
            $md .= $this->generateTimelineMarkdown($timeline);
            $md .= "\n---\n\n";
        }

        return $md;
    }

    /**
     * Generate timeline Markdown content.
     */
    protected function generateTimelineMarkdown(Timeline $timeline): string
    {
        $timeline->load('scenes');

        $md = "## {$timeline->name}";
        if ($timeline->is_canon) {
            $md .= " (Canon)";
        }
        $md .= "\n\n";

        if ($timeline->description) {
            $md .= "*{$timeline->description}*\n\n";
        }

        foreach ($timeline->scenes->sortBy('order') as $scene) {
            $md .= $this->generateSceneMarkdown($scene, 3);
        }

        return $md;
    }

    /**
     * Generate scene Markdown content.
     */
    protected function generateSceneMarkdown(Scene $scene, int $headingLevel = 1): string
    {
        $heading = str_repeat('#', $headingLevel);
        $md = "{$heading} {$scene->title}\n\n";

        // Metadata
        $metadata = [];
        if ($scene->date) {
            $metadata[] = "**Date:** {$scene->date}";
        }
        if ($scene->location) {
            $metadata[] = "**Location:** {$scene->location}";
        }
        if ($scene->mood) {
            $metadata[] = "**Mood:** {$scene->mood}";
        }
        if ($scene->pov) {
            $metadata[] = "**POV:** {$scene->pov}";
        }
        if ($scene->is_branch_point) {
            $metadata[] = "**Branch Point**";
            if ($scene->branch_question) {
                $metadata[] = "*{$scene->branch_question}*";
            }
        }

        if (!empty($metadata)) {
            $md .= implode(" | ", $metadata) . "\n\n";
        }

        // Characters
        if ($scene->characters && $scene->characters->isNotEmpty()) {
            $md .= "**Characters:** " . $scene->characters->pluck('name')->join(', ') . "\n\n";
        }

        // Tags
        if ($scene->tags && $scene->tags->isNotEmpty()) {
            $md .= "**Tags:** " . $scene->tags->pluck('name')->map(fn ($t) => "#{$t}")->join(' ') . "\n\n";
        }

        // Summary
        if ($scene->summary) {
            $md .= "> {$scene->summary}\n\n";
        }

        // Content
        if ($scene->content) {
            // Strip HTML and convert to plain text
            $content = strip_tags($scene->content);
            $content = html_entity_decode($content);
            $md .= "{$content}\n\n";
        }

        return $md;
    }

    /**
     * Sanitize filename for safe download.
     */
    protected function sanitizeFilename(string $name): string
    {
        // Remove/replace invalid characters
        $name = preg_replace('/[\/\\\\:*?"<>|]/', '_', $name);
        // Replace spaces with underscores
        $name = str_replace(' ', '_', $name);
        // Remove multiple underscores
        $name = preg_replace('/_+/', '_', $name);
        // Trim underscores from ends
        $name = trim($name, '_');
        // Limit length
        return substr($name, 0, 100);
    }
}
