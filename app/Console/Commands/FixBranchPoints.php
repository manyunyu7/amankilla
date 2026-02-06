<?php

namespace App\Console\Commands;

use App\Models\Scene;
use App\Models\Timeline;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixBranchPoints extends Command
{
    protected $signature = 'import:fix-branch-points';
    protected $description = 'Mark branch points and link branching timelines';

    public function handle()
    {
        $this->info('Fixing branch points for INFJ × INFP Journey universe...');
        $this->newLine();

        DB::beginTransaction();

        try {
            // Get the universe
            $universe = \App\Models\Universe::where('name', 'INFJ × INFP Journey')->first();

            if (!$universe) {
                $this->error('Universe not found!');
                return 1;
            }

            $timelines = $universe->timelines->keyBy('name');

            // 1. Mark the last scene in Rania & Mama - Original as a branch point
            $originalTimeline = $timelines->get('Rania & Mama - Original');
            $healthyTimeline = $timelines->get('Rania & Mama - Healthy');

            if ($originalTimeline && $healthyTimeline) {
                // Find the last scene before the healthy version branches off
                $lastOriginalScene = $originalTimeline->scenes()
                    ->orderBy('order', 'desc')
                    ->first();

                if ($lastOriginalScene) {
                    $lastOriginalScene->update([
                        'is_branch_point' => true,
                        'branch_question' => 'What if Mama learns to adjust to Rania\'s personality?'
                    ]);

                    // Update healthy timeline to reference this branch point
                    $healthyTimeline->update([
                        'branch_from_id' => $lastOriginalScene->id
                    ]);

                    $this->info("✅ Marked branch point: {$lastOriginalScene->title}");
                    $this->line("   Branch question: What if Mama learns to adjust?");
                    $this->line("   Branches to: Rania & Mama - Healthy timeline");
                    $this->newLine();
                }
            }

            // 2. Look for scenes in Primary Story that could be branch points
            // These are scenes where key decisions happen or relationship milestones occur
            $primaryTimeline = $timelines->get('Primary Story');

            if ($primaryTimeline) {
                // Find scenes with specific content that indicate decision points
                $potentialBranches = $primaryTimeline->scenes()
                    ->where(function($query) {
                        $query->where('content', 'like', '%resign%')
                              ->orWhere('content', 'like', '%2027%')
                              ->orWhere('content', 'like', '%timeline%')
                              ->orWhere('title', 'like', '%goodbye%')
                              ->orWhere('title', 'like', '%pulang%');
                    })
                    ->get();

                $this->info("Found {$potentialBranches->count()} potential branch points in Primary Story");
                $this->newLine();

                // Let's mark some key decision points
                $keyScenes = [
                    'resign' => 'What if he doesn\'t resign? What if the relationship continues long-distance?',
                    '2027' => 'What if the timeline changes? What if they don\'t meet the 2027 deadline?',
                    'Goodbye' => 'What if this isn\'t goodbye? What if they decide to fight for the relationship?',
                ];

                foreach ($keyScenes as $keyword => $question) {
                    $scene = $primaryTimeline->scenes()
                        ->where(function($query) use ($keyword) {
                            $query->where('content', 'like', "%{$keyword}%")
                                  ->orWhere('title', 'like', "%{$keyword}%");
                        })
                        ->orderBy('order', 'asc')
                        ->first();

                    if ($scene && !$scene->is_branch_point) {
                        $scene->update([
                            'is_branch_point' => true,
                            'branch_question' => $question
                        ]);

                        $this->info("✅ Marked branch point: {$scene->title}");
                        $this->line("   Branch question: {$question}");
                        $this->newLine();
                    }
                }
            }

            // 3. Mark MBTI Perspectives timeline branch
            $mbtiTimeline = $timelines->get('MBTI Perspectives');
            if ($mbtiTimeline && $primaryTimeline) {
                // Find a scene in primary story that discusses the relationship
                $analysisPoint = $primaryTimeline->scenes()
                    ->where('content', 'like', '%INFJ%')
                    ->where('content', 'like', '%INFP%')
                    ->orderBy('order', 'asc')
                    ->skip(10) // Skip the very early scenes
                    ->first();

                if ($analysisPoint && !$analysisPoint->is_branch_point) {
                    $analysisPoint->update([
                        'is_branch_point' => true,
                        'branch_question' => 'How would different personality types analyze this relationship?'
                    ]);

                    $mbtiTimeline->update([
                        'branch_from_id' => $analysisPoint->id
                    ]);

                    $this->info("✅ Marked MBTI analysis branch point");
                    $this->line("   Branches to: MBTI Perspectives timeline");
                    $this->newLine();
                }
            }

            // Get final statistics
            $totalBranchPoints = Scene::whereHas('timeline', function($q) use ($universe) {
                $q->where('universe_id', $universe->id);
            })->where('is_branch_point', true)->count();

            $timelinesWithBranch = Timeline::where('universe_id', $universe->id)
                ->whereNotNull('branch_from_id')
                ->count();

            DB::commit();

            $this->newLine();
            $this->info('=== Branch Points Summary ===');
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Branch Points', $totalBranchPoints],
                    ['Timelines with Branch Links', $timelinesWithBranch],
                ]
            );

            $this->newLine();
            $this->info('✅ Branch points fixed successfully!');

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to fix branch points: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
