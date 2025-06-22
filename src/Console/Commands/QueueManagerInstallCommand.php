<?php

namespace HenningD\LaravelQueueManager\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class QueueManagerInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue-manager:install {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Queue Manager package';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Installing Laravel Queue Manager...');

        // Publish configuration
        $this->call('vendor:publish', [
            '--provider' => 'HenningD\LaravelQueueManager\QueueManagerServiceProvider',
            '--tag' => 'config',
            '--force' => $this->option('force')
        ]);

        // Publish migrations
        $this->call('vendor:publish', [
            '--provider' => 'HenningD\LaravelQueueManager\QueueManagerServiceProvider',
            '--tag' => 'migrations',
            '--force' => $this->option('force')
        ]);

        // Publish views
        $this->call('vendor:publish', [
            '--provider' => 'HenningD\LaravelQueueManager\QueueManagerServiceProvider',
            '--tag' => 'views',
            '--force' => $this->option('force')
        ]);

        // Run migrations
        if ($this->confirm('Do you want to run the migrations now?', true)) {
            $this->call('migrate');
        }

        // Create default queue configurations
        if ($this->confirm('Do you want to create default queue configurations?', true)) {
            $this->call('queue-manager:seed');
        }

        $this->info('Queue Manager installed successfully!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Configure your queue settings in config/queue-manager.php');
        $this->line('2. Visit /queue-manager to access the dashboard');
        $this->line('3. Create your first worker with: php artisan queue-manager:worker:create');
    }
}