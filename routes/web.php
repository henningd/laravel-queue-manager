<?php

use Illuminate\Support\Facades\Route;
use HenningD\LaravelQueueManager\Http\Controllers\QueueManagerController;

Route::group([
    'prefix' => config('queue-manager.route.prefix', 'queue-manager'),
    'middleware' => config('queue-manager.route.middleware', ['web']),
    'as' => config('queue-manager.route.name', 'queue-manager.'),
], function () {
    
    // Dashboard
    Route::get('/', [QueueManagerController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [QueueManagerController::class, 'dashboard'])->name('dashboard.index');
    
    // Status API
    Route::get('/status', [QueueManagerController::class, 'status'])->name('status');
    Route::get('/diagnose', [QueueManagerController::class, 'diagnose'])->name('diagnose');
    
    // Worker Management
    Route::get('/workers', [QueueManagerController::class, 'getWorkers'])->name('workers.index');
    Route::post('/workers', [QueueManagerController::class, 'createWorker'])->name('workers.create');
    Route::put('/workers/{id}', [QueueManagerController::class, 'updateWorker'])->name('workers.update');
    Route::delete('/workers/{id}', [QueueManagerController::class, 'deleteWorker'])->name('workers.delete');
    Route::post('/workers/{id}/start', [QueueManagerController::class, 'startWorker'])->name('workers.start');
    Route::post('/workers/{id}/stop', [QueueManagerController::class, 'stopWorker'])->name('workers.stop');
    Route::post('/workers/{id}/restart', [QueueManagerController::class, 'restartWorker'])->name('workers.restart');
    
    // Queue Configuration
    Route::get('/queues', [QueueManagerController::class, 'getQueues'])->name('queues.index');
    Route::post('/queues', [QueueManagerController::class, 'createQueue'])->name('queues.create');
    Route::put('/queues/{id}', [QueueManagerController::class, 'updateQueue'])->name('queues.update');
    Route::delete('/queues/{id}', [QueueManagerController::class, 'deleteQueue'])->name('queues.delete');
    Route::post('/queues/{id}/start-workers', [QueueManagerController::class, 'startQueueWorkers'])->name('queues.start-workers');
    Route::post('/queues/{id}/stop-workers', [QueueManagerController::class, 'stopQueueWorkers'])->name('queues.stop-workers');
    Route::post('/queues/{id}/clear-jobs', [QueueManagerController::class, 'clearQueueJobs'])->name('queues.clear-jobs');
    Route::post('/queues/{id}/clear-failed', [QueueManagerController::class, 'clearQueueFailedJobs'])->name('queues.clear-failed');
    
    // Job Management
    Route::post('/start-worker', [QueueManagerController::class, 'startWorkerProcess'])->name('start-worker');
    Route::post('/restart-workers', [QueueManagerController::class, 'restartWorkers'])->name('restart-workers');
    Route::post('/retry-failed', [QueueManagerController::class, 'retryFailedJobs'])->name('retry-failed');
    Route::post('/flush-failed-by-queue', [QueueManagerController::class, 'flushFailedJobsByQueue'])->name('flush-failed-by-queue');
    Route::post('/kill-worker', [QueueManagerController::class, 'killWorker'])->name('kill-worker');
    Route::post('/kill-multiple-workers', [QueueManagerController::class, 'killMultipleWorkers'])->name('kill-multiple-workers');
    Route::post('/delete-job', [QueueManagerController::class, 'deleteJob'])->name('delete-job');
});