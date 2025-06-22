<?php

namespace HenningD\LaravelQueueManager\Console\Commands;

use Illuminate\Console\Command;
use HenningD\LaravelQueueManager\Models\QueueWorker;

class WorkerListCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue-manager:worker:list 
                            {--queue= : Filter by queue name}
                            {--status= : Filter by status (running|stopped|all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all queue workers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $query = QueueWorker::query();

        // Filter by queue
        if ($queue = $this->option('queue')) {
            $query->where('queue', $queue);
        }

        // Filter by status
        if ($status = $this->option('status')) {
            switch ($status) {
                case 'running':
                    $query->where('is_running', true);
                    break;
                case 'stopped':
                    $query->where('is_running', false);
                    break;
                case 'all':
                default:
                    // No filter
                    break;
            }
        }

        $workers = $query->orderBy('name')->get();

        if ($workers->isEmpty()) {
            $this->info('No workers found.');
            return 0;
        }

        $headers = ['ID', 'Name', 'Queue', 'Status', 'PID', 'Memory', 'Timeout', 'Started At'];
        $rows = [];

        foreach ($workers as $worker) {
            $rows[] = [
                $worker->id,
                $worker->name,
                $worker->queue,
                $worker->is_running ? '<fg=green>Running</>' : '<fg=red>Stopped</>',
                $worker->process_id ?: 'N/A',
                $worker->memory . 'MB',
                $worker->timeout . 's',
                $worker->started_at ? $worker->started_at->format('Y-m-d H:i:s') : 'Never'
            ];
        }

        $this->table($headers, $rows);

        // Summary
        $total = $workers->count();
        $running = $workers->where('is_running', true)->count();
        $stopped = $workers->where('is_running', false)->count();

        $this->line('');
        $this->info("Total: {$total} workers");
        $this->line("<fg=green>Running: {$running}</>");
        $this->line("<fg=red>Stopped: {$stopped}</>");

        return 0;
    }
}