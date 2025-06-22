<?php

namespace HenningD\LaravelQueueManager\Console\Commands;

use Illuminate\Console\Command;
use HenningD\LaravelQueueManager\Models\QueueWorker;

class WorkerStartCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue-manager:worker:start 
                            {worker? : The worker ID or name to start}
                            {--all : Start all workers}
                            {--queue= : Start all workers for a specific queue}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start queue workers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            return $this->startAllWorkers();
        }

        if ($queue = $this->option('queue')) {
            return $this->startWorkersByQueue($queue);
        }

        $workerIdentifier = $this->argument('worker');
        if (!$workerIdentifier) {
            $this->error('Please specify a worker ID/name, use --all, or --queue option.');
            return 1;
        }

        return $this->startWorker($workerIdentifier);
    }

    protected function startWorker($identifier)
    {
        $worker = is_numeric($identifier) 
            ? QueueWorker::find($identifier)
            : QueueWorker::where('name', $identifier)->first();

        if (!$worker) {
            $this->error("Worker '{$identifier}' not found.");
            return 1;
        }

        if ($worker->is_running) {
            $this->warn("Worker '{$worker->name}' is already running.");
            return 0;
        }

        try {
            $worker->start();
            $this->info("Worker '{$worker->name}' started successfully.");
            return 0;
        } catch (\Exception $e) {
            $this->error("Failed to start worker '{$worker->name}': " . $e->getMessage());
            return 1;
        }
    }

    protected function startAllWorkers()
    {
        $workers = QueueWorker::where('is_active', true)
            ->where('is_running', false)
            ->get();

        if ($workers->isEmpty()) {
            $this->info('No stopped workers found.');
            return 0;
        }

        $started = 0;
        $failed = 0;

        foreach ($workers as $worker) {
            try {
                $worker->start();
                $this->line("✓ Started worker '{$worker->name}'");
                $started++;
            } catch (\Exception $e) {
                $this->error("✗ Failed to start worker '{$worker->name}': " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("Started {$started} workers, {$failed} failed.");
        return $failed > 0 ? 1 : 0;
    }

    protected function startWorkersByQueue($queue)
    {
        $workers = QueueWorker::where('queue', $queue)
            ->where('is_active', true)
            ->where('is_running', false)
            ->get();

        if ($workers->isEmpty()) {
            $this->info("No stopped workers found for queue '{$queue}'.");
            return 0;
        }

        $started = 0;
        $failed = 0;

        foreach ($workers as $worker) {
            try {
                $worker->start();
                $this->line("✓ Started worker '{$worker->name}'");
                $started++;
            } catch (\Exception $e) {
                $this->error("✗ Failed to start worker '{$worker->name}': " . $e->getMessage());
                $failed++;
            }
        }

        $this->info("Started {$started} workers for queue '{$queue}', {$failed} failed.");
        return $failed > 0 ? 1 : 0;
    }
}