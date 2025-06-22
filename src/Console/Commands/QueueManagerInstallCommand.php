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

        // Add routes to web.php
        if ($this->confirm('Do you want to add Queue Manager routes to routes/web.php?', true)) {
            $this->addRoutesToWebFile();
        }

        $this->info('Queue Manager installed successfully!');
        $this->line('');
        $this->line('Next steps:');
        $this->line('1. Configure your queue settings in config/queue-manager.php');
        $this->line('2. Visit /queue-manager to access the dashboard');
        $this->line('3. Create your first worker with: php artisan queue-manager:worker:create');
    }

    /**
     * Add Queue Manager routes to routes/web.php
     */
    protected function addRoutesToWebFile()
    {
        $webRoutesPath = base_path('routes/web.php');
        
        if (!File::exists($webRoutesPath)) {
            $this->error('routes/web.php file not found!');
            return;
        }

        $webRoutesContent = File::get($webRoutesPath);
        
        // Check if routes are already added
        if (strpos($webRoutesContent, 'queue-manager') !== false) {
            $this->info('Queue Manager routes already exist in routes/web.php');
            return;
        }

        $routesToAdd = "\n// Queue Manager Routes\n// Note: You can also use the automatic routes by removing this block\n// and letting the ServiceProvider handle routing automatically\nRoute::group([\n    'prefix' => config('queue-manager.route.prefix', 'queue-manager'),\n    'middleware' => config('queue-manager.route.middleware', ['web']),\n    'as' => 'queue-manager.',\n], function () {\n    Route::get('/', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'dashboard'])->name('dashboard');\n    Route::get('/dashboard', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'dashboard'])->name('dashboard.index');\n    \n    // API Routes\n    Route::get('/status', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'status'])->name('status');\n    Route::get('/workers', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'getWorkers'])->name('workers.index');\n    Route::post('/workers', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'createWorker'])->name('workers.create');\n    Route::post('/workers/{id}/start', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'startWorker'])->name('workers.start');\n    Route::post('/workers/{id}/stop', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'stopWorker'])->name('workers.stop');\n    Route::post('/workers/{id}/restart', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'restartWorker'])->name('workers.restart');\n    Route::delete('/workers/{id}', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'deleteWorker'])->name('workers.delete');\n    \n    Route::get('/queues', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'getQueues'])->name('queues.index');\n    Route::post('/queues', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'createQueue'])->name('queues.create');\n    Route::post('/queues/{id}/start-workers', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'startQueueWorkers'])->name('queues.start-workers');\n    Route::post('/queues/{id}/stop-workers', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'stopQueueWorkers'])->name('queues.stop-workers');\n    Route::post('/queues/{id}/clear-jobs', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'clearQueueJobs'])->name('queues.clear-jobs');\n    Route::post('/queues/{id}/clear-failed', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'clearQueueFailedJobs'])->name('queues.clear-failed');\n    \n    Route::post('/restart-workers', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'restartWorkers'])->name('restart-workers');\n    Route::post('/retry-failed', [\\HenningD\\LaravelQueueManager\\Http\\Controllers\\QueueManagerController::class, 'retryFailedJobs'])->name('retry-failed');\n});\n";

        // Add routes at the end of the file
        File::put($webRoutesPath, $webRoutesContent . $routesToAdd);
        
        $this->info('✓ Queue Manager routes added to routes/web.php');
        $this->line('  Added routes:');
        $this->line('    - GET /queue-manager (dashboard)');
        $this->line('    - POST /queue-manager/workers (create worker)');
        $this->line('    - POST /queue-manager/workers/{id}/start (start worker)');
        $this->line('    - And all other API endpoints...');
        $this->line('');
        $this->line('  Note: You can remove these routes from web.php if you prefer');
        $this->line('        to use automatic routing via the ServiceProvider.');
    }
}