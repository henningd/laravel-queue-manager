<?php

namespace HenningD\LaravelQueueManager\Console\Commands;

use Illuminate\Console\Command;
use HenningD\LaravelQueueManager\Models\QueueConfiguration;
use HenningD\LaravelQueueManager\Models\QueueWorker;

class QueueManagerSeedCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue-manager:seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed default queue configurations and workers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Seeding default queue configurations...');

        // Create default queue configurations
        $defaultQueues = [
            [
                'name' => 'default',
                'display_name' => 'Standard Queue',
                'description' => 'Standard Queue für allgemeine Jobs',
                'priority' => 1,
                'is_active' => true,
                'max_jobs_per_minute' => 60,
            ],
            [
                'name' => 'high',
                'display_name' => 'High Priority Queue',
                'description' => 'Hochpriorisierte Queue für wichtige Jobs',
                'priority' => 10,
                'is_active' => true,
                'max_jobs_per_minute' => 120,
            ],
            [
                'name' => 'low',
                'display_name' => 'Low Priority Queue',
                'description' => 'Niedrigpriorisierte Queue für Hintergrund-Jobs',
                'priority' => 1,
                'is_active' => true,
                'max_jobs_per_minute' => 30,
            ],
            [
                'name' => 'emails',
                'display_name' => 'E-Mail Queue',
                'description' => 'Queue für E-Mail-Versand',
                'priority' => 5,
                'is_active' => true,
                'max_jobs_per_minute' => 100,
            ],
            [
                'name' => 'reports',
                'display_name' => 'Report Queue',
                'description' => 'Queue für Report-Generierung',
                'priority' => 3,
                'is_active' => true,
                'max_jobs_per_minute' => 10,
            ]
        ];

        foreach ($defaultQueues as $queueData) {
            $queue = QueueConfiguration::firstOrCreate(
                ['name' => $queueData['name']],
                $queueData
            );

            if ($queue->wasRecentlyCreated) {
                $this->line("✓ Queue '{$queueData['name']}' erstellt");
            } else {
                $this->line("- Queue '{$queueData['name']}' existiert bereits");
            }
        }

        // Create default workers
        $this->info('Creating default workers...');

        $defaultWorkers = [
            [
                'name' => 'Default Worker 1',
                'queue' => 'default',
                'timeout' => 60,
                'memory' => 128,
                'sleep' => 3,
                'max_tries' => 3,
                'auto_start' => true,
                'is_active' => true,
            ],
            [
                'name' => 'High Priority Worker',
                'queue' => 'high',
                'timeout' => 120,
                'memory' => 256,
                'sleep' => 1,
                'max_tries' => 5,
                'auto_start' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Email Worker',
                'queue' => 'emails',
                'timeout' => 30,
                'memory' => 128,
                'sleep' => 2,
                'max_tries' => 3,
                'auto_start' => true,
                'is_active' => true,
            ]
        ];

        foreach ($defaultWorkers as $workerData) {
            $worker = QueueWorker::firstOrCreate(
                ['name' => $workerData['name']],
                $workerData
            );

            if ($worker->wasRecentlyCreated) {
                $this->line("✓ Worker '{$workerData['name']}' erstellt");
            } else {
                $this->line("- Worker '{$workerData['name']}' existiert bereits");
            }
        }

        $this->info('Default configurations seeded successfully!');
        $this->line('');
        $this->line('Created queues: ' . implode(', ', array_column($defaultQueues, 'name')));
        $this->line('Created workers: ' . implode(', ', array_column($defaultWorkers, 'name')));
    }
}