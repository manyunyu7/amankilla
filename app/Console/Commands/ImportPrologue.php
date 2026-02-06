<?php

namespace App\Console\Commands;

use App\Models\Scene;
use App\Models\Timeline;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportPrologue extends Command
{
    protected $signature = 'import:add-prologue';
    protected $description = 'Add missing prologue content from raw.md lines 2-5';

    public function handle()
    {
        $this->info('Adding prologue to INFJ × INFP Journey...');
        $this->newLine();

        DB::beginTransaction();

        try {
            // Get the universe and primary timeline
            $universe = \App\Models\Universe::where('name', 'INFJ × INFP Journey')->first();

            if (!$universe) {
                $this->error('Universe not found!');
                return 1;
            }

            $primaryTimeline = $universe->timelines()->where('name', 'Primary Story')->first();

            if (!$primaryTimeline) {
                $this->error('Primary Story timeline not found!');
                return 1;
            }

            // Check if prologue already exists
            $existingPrologue = $primaryTimeline->scenes()
                ->where('title', 'Prologue: The Question')
                ->first();

            if ($existingPrologue) {
                $this->warn('Prologue already exists!');
                return 0;
            }

            // Get all existing scenes and shift their order
            $this->info('Shifting existing scenes order...');
            $existingScenes = $primaryTimeline->scenes()->orderBy('order', 'asc')->get();

            foreach ($existingScenes as $scene) {
                $scene->update(['order' => $scene->order + 1]);
            }

            $this->info('Shifted ' . $existingScenes->count() . ' scenes');

            // Create prologue scene with the missing content
            $prologueContent = "Bagaimana tipikal infj saat mau jalan bareng sama cowoknya infp?

Infj posisinya sudah di bandung, infp baru berangkat malemnya, mereka besok pagi mau jalan2 naik kereta ke garut";

            $prologue = Scene::create([
                'timeline_id' => $primaryTimeline->id,
                'title' => 'Prologue: The Question',
                'content' => $prologueContent,
                'summary' => 'Opening question that sets up the story: How does a typical INFJ act when going out with their INFP boyfriend? Setting: INFJ in Bandung, INFP traveling at night, planning to take the train to Garut tomorrow morning.',
                'order' => 0, // Put at the very beginning
                'word_count' => str_word_count($prologueContent),
                'mood' => 'anticipation',
            ]);

            // Link INFJ and INFP characters
            $infj = $universe->characters()->where('name', 'INFJ')->first();
            $infp = $universe->characters()->where('name', 'INFP')->first();

            if ($infj) {
                $prologue->characters()->attach($infj->id);
            }
            if ($infp) {
                $prologue->characters()->attach($infp->id);
            }

            // Add relevant tags
            $chatTag = $universe->tags()->where('name', 'Chat Conversation')->first();
            if ($chatTag) {
                $prologue->tags()->attach($chatTag->id);
            }

            DB::commit();

            $this->newLine();
            $this->info('✅ Prologue added successfully!');
            $this->line('   Title: ' . $prologue->title);
            $this->line('   Order: ' . $prologue->order);
            $this->line('   Word count: ' . $prologue->word_count);
            $this->newLine();
            $this->info('Total scenes in Primary Story: ' . ($existingScenes->count() + 1));

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Failed to add prologue: ' . $e->getMessage());
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
