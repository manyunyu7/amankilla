<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\RawImporter;
use Illuminate\Console\Command;

class ImportRawMd extends Command
{
    protected $signature = 'import:raw-md
                            {user_id : The ID of the user to import for}
                            {--dry-run : Preview import without saving}';

    protected $description = 'Import story from raw.md file';

    public function handle()
    {
        $userId = $this->argument('user_id');
        $dryRun = $this->option('dry-run');

        // Verify user exists
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found");
            return 1;
        }

        $filePath = base_path('raw.md');

        if (!file_exists($filePath)) {
            $this->error('raw.md file not found in project root');
            return 1;
        }

        $this->info("Starting import from {$filePath}");
        $this->info("User: {$user->username} (ID: {$user->id})");
        $this->info("Mode: " . ($dryRun ? "DRY RUN (no changes will be saved)" : "LIVE"));
        $this->newLine();

        if (!$dryRun && !$this->confirm('This will import the entire raw.md file. Continue?', true)) {
            $this->info('Import cancelled');
            return 0;
        }

        try {
            $importer = new RawImporter($filePath);

            $progressBar = $this->output->createProgressBar();
            $progressBar->setFormat('verbose');

            $result = $importer->import($userId, $dryRun);

            $progressBar->finish();
            $this->newLine(2);

            if ($result['success']) {
                $this->info('Import completed successfully!');
                $this->newLine();

                $this->table(
                    ['Metric', 'Count'],
                    [
                        ['Scenes Imported', $result['stats']['scenes_imported']],
                        ['Scenes Skipped', $result['stats']['scenes_skipped']],
                        ['Characters Created', $result['stats']['characters_created']],
                        ['Timelines Created', $result['stats']['timelines_created']],
                        ['Tags Created', $result['stats']['tags_created']],
                        ['Errors', count($result['stats']['errors'])],
                    ]
                );

                if (!empty($result['stats']['errors'])) {
                    $this->newLine();
                    $this->warn('Errors encountered:');
                    foreach ($result['stats']['errors'] as $error) {
                        $this->line("  - {$error}");
                    }
                }

                if ($dryRun) {
                    $this->newLine();
                    $this->comment('DRY RUN - No changes were saved to the database');
                } else {
                    $this->newLine();
                    $this->info("Universe ID: {$result['universe_id']}");
                }

                $this->newLine();
                $this->line('Import Log:');
                foreach (array_slice($result['log'], -10) as $logEntry) {
                    $this->line("  {$logEntry}");
                }

                return 0;
            } else {
                $this->error('Import failed!');
                $this->error($result['error'] ?? 'Unknown error');

                if (!empty($result['log'])) {
                    $this->newLine();
                    $this->line('Import Log:');
                    foreach (array_slice($result['log'], -20) as $logEntry) {
                        $this->line("  {$logEntry}");
                    }
                }

                return 1;
            }

        } catch (\Exception $e) {
            $this->error('Import failed with exception:');
            $this->error($e->getMessage());
            $this->newLine();
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
