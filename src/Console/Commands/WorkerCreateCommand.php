<?php

namespace HenningD\LaravelQueueManager\Console\Commands;

use Illuminate\Console\Command;
use HenningD\LaravelQueueManager\Models\QueueWorker;

class WorkerCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue-manager:worker:create 
                            {name : The name of the worker}
                            {--queue=default : The queue to process}
                            {--timeout=60 : The timeout in seconds}
                            {--memory=128 : The memory limit in MB}
                            {--sleep=3 : The sleep time between jobs}
                            {--tries=3 : The maximum number of tries}
                            {--start : Start the worker immediately}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new queue worker';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');
        
        // Check if worker already exists
        if (QueueWorker::where('name', $name)->exists()) {
            $this->error("Worker '{$name}' already exists!");
            return 1;
        }

        $worker = QueueWorker::create([
            'name' => $name,
            'queue' => $this->option('queue'),
            'timeout' => $this->option('timeout'),
            'memory' => $this->option('memory'),
            'sleep' => $this->option('sleep'),
            'max_tries' => $this->option('tries'),
            'auto_start' => $this->option('start'),
            'is_active' => true,
        ]);

        $this->info("Worker '{$name}' created successfully!");
        
        // Display worker details
        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $worker->id],
                ['Name', $worker->name],
                ['Queue', $worker->queue],
                ['Timeout', $worker->timeout . 's'],
                ['Memory', $worker->memory . 'MB'],
                ['Sleep', $worker->sleep . 's'],
                ['Max Tries', $worker->max_tries],
                ['Auto Start', $worker->auto_start ? 'Yes' : 'No'],
                ['Status', $worker->is_active ? 'Active' : 'Inactive'],
            ]
        );

        if ($this->option('start')) {
            $this->info('Starting worker...');
            try {
                $worker->start();
                $this->info("Worker '{$name}' started successfully!");
            } catch (\Exception $e) {
                $this->error("Failed to start worker: " . $e->getMessage());
            }
        }

        return 0;
    }
}