# Erweiterte Funktionen

Diese Anleitung zeigt dir die erweiterten Funktionen des Laravel Queue Manager Packages. Du lernst professionelle Features kennen, die über die Grundfunktionalität hinausgehen.

## 🎯 Übersicht

Erweiterte Funktionen umfassen:

1. **Auto-Scaling** - Automatische Worker-Skalierung
2. **Job-Batching** - Gruppierte Job-Verarbeitung
3. **Priority-Queues** - Prioritätsbasierte Verarbeitung
4. **Rate-Limiting** - Durchsatz-Kontrolle
5. **Circuit-Breaker** - Fehlerresistenz
6. **Job-Chaining** - Verkettete Job-Ausführung
7. **Scheduled-Jobs** - Zeitgesteuerte Ausführung
8. **Multi-Tenant-Support** - Mandantenfähigkeit
9. **Monitoring & Alerting** - Erweiterte Überwachung
10. **API-Integration** - Externe Schnittstellen

## 🔄 Auto-Scaling

### Intelligente Worker-Skalierung

#### Konfiguration

```php
// config/queue-manager.php
'auto_scaling' => [
    'enabled' => true,
    'check_interval' => 60, // Sekunden
    'strategies' => [
        'queue_length' => [
            'enabled' => true,
            'scale_up_threshold' => 20,
            'scale_down_threshold' => 5,
            'max_scale_factor' => 3,
        ],
        'cpu_usage' => [
            'enabled' => true,
            'scale_up_threshold' => 80, // Prozent
            'scale_down_threshold' => 30,
        ],
        'response_time' => [
            'enabled' => true,
            'scale_up_threshold' => 30, // Sekunden
            'scale_down_threshold' => 10,
        ],
    ],
    'queue_configs' => [
        'high-priority' => [
            'min_workers' => 2,
            'max_workers' => 10,
            'scale_factor' => 2,
        ],
        'default' => [
            'min_workers' => 1,
            'max_workers' => 8,
            'scale_factor' => 1.5,
        ],
        'background' => [
            'min_workers' => 1,
            'max_workers' => 3,
            'scale_factor' => 1.2,
        ],
    ],
],
```

#### Auto-Scaling-Service

```php
// app/Services/AutoScalingService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use HenningD\LaravelQueueManager\Models\QueueWorker;
use HenningD\LaravelQueueManager\Models\QueueConfiguration;

class AutoScalingService
{
    private $config;
    private $metrics;
    
    public function __construct()
    {
        $this->config = config('queue-manager.auto_scaling');
        $this->metrics = app(MetricsCollectorService::class);
    }
    
    public function scale()
    {
        if (!$this->config['enabled']) {
            return;
        }
        
        $queues = QueueConfiguration::where('auto_scale', true)->get();
        
        foreach ($queues as $queue) {
            $this->scaleQueue($queue);
        }
    }
    
    private function scaleQueue($queueConfig)
    {
        $queueName = $queueConfig->name;
        $currentWorkers = $this->getCurrentWorkerCount($queueName);
        $targetWorkers = $this->calculateTargetWorkers($queueConfig);
        
        if ($targetWorkers > $currentWorkers) {
            $this->scaleUp($queueConfig, $targetWorkers - $currentWorkers);
        } elseif ($targetWorkers < $currentWorkers) {
            $this->scaleDown($queueConfig, $currentWorkers - $targetWorkers);
        }
    }
    
    private function calculateTargetWorkers($queueConfig)
    {
        $queueName = $queueConfig->name;
        $currentWorkers = $this->getCurrentWorkerCount($queueName);
        $minWorkers = $queueConfig->min_workers ?? 1;
        $maxWorkers = $queueConfig->max_workers ?? 10;
        
        $scalingFactors = [];
        
        // Queue-Length-basierte Skalierung
        if ($this->config['strategies']['queue_length']['enabled']) {
            $queueLength = $this->getQueueLength($queueName);
            $scalingFactors[] = $this->calculateQueueLengthFactor($queueLength);
        }
        
        // CPU-Usage-basierte Skalierung
        if ($this->config['strategies']['cpu_usage']['enabled']) {
            $cpuUsage = $this->getAverageCpuUsage($queueName);
            $scalingFactors[] = $this->calculateCpuUsageFactor($cpuUsage);
        }
        
        // Response-Time-basierte Skalierung
        if ($this->config['strategies']['response_time']['enabled']) {
            $responseTime = $this->getAverageResponseTime($queueName);
            $scalingFactors[] = $this->calculateResponseTimeFactor($responseTime);
        }
        
        // Höchsten Skalierungsfaktor verwenden
        $maxFactor = max($scalingFactors);
        $targetWorkers = ceil($currentWorkers * $maxFactor);
        
        return max($minWorkers, min($maxWorkers, $targetWorkers));
    }
    
    private function calculateQueueLengthFactor($queueLength)
    {
        $config = $this->config['strategies']['queue_length'];
        
        if ($queueLength >= $config['scale_up_threshold']) {
            return min($config['max_scale_factor'], 1 + ($queueLength / $config['scale_up_threshold']));
        } elseif ($queueLength <= $config['scale_down_threshold']) {
            return max(0.5, $queueLength / $config['scale_down_threshold']);
        }
        
        return 1.0; // Keine Skalierung
    }
    
    private function calculateCpuUsageFactor($cpuUsage)
    {
        $config = $this->config['strategies']['cpu_usage'];
        
        if ($cpuUsage >= $config['scale_up_threshold']) {
            return 1.5; // 50% mehr Worker
        } elseif ($cpuUsage <= $config['scale_down_threshold']) {
            return 0.8; // 20% weniger Worker
        }
        
        return 1.0;
    }
    
    private function calculateResponseTimeFactor($responseTime)
    {
        $config = $this->config['strategies']['response_time'];
        
        if ($responseTime >= $config['scale_up_threshold']) {
            return 1.3; // 30% mehr Worker
        } elseif ($responseTime <= $config['scale_down_threshold']) {
            return 0.9; // 10% weniger Worker
        }
        
        return 1.0;
    }
    
    private function scaleUp($queueConfig, $count)
    {
        for ($i = 0; $i < $count; $i++) {
            $worker = QueueWorker::create([
                'name' => "auto-{$queueConfig->name}-" . uniqid(),
                'display_name' => "Auto Worker {$queueConfig->name}",
                'queue' => $queueConfig->name,
                'timeout' => $queueConfig->timeout ?? 60,
                'memory' => $queueConfig->memory ?? 128,
                'sleep' => $queueConfig->sleep ?? 3,
                'tries' => $queueConfig->tries ?? 3,
                'auto_start' => true,
                'auto_managed' => true,
            ]);
            
            $worker->start();
        }
        
        Log::info("Auto-scaled up queue {$queueConfig->name} by {$count} workers");
        
        $this->metrics->record('auto_scaling.scale_up', [
            'queue' => $queueConfig->name,
            'count' => $count,
            'timestamp' => now(),
        ]);
    }
    
    private function scaleDown($queueConfig, $count)
    {
        $workers = QueueWorker::where('queue', $queueConfig->name)
            ->where('auto_managed', true)
            ->where('status', 'running')
            ->limit($count)
            ->get();
            
        foreach ($workers as $worker) {
            $worker->gracefulStop();
        }
        
        Log::info("Auto-scaled down queue {$queueConfig->name} by {$count} workers");
        
        $this->metrics->record('auto_scaling.scale_down', [
            'queue' => $queueConfig->name,
            'count' => $count,
            'timestamp' => now(),
        ]);
    }
    
    private function getCurrentWorkerCount($queueName)
    {
        return QueueWorker::where('queue', $queueName)
            ->where('status', 'running')
            ->count();
    }
    
    private function getQueueLength($queueName)
    {
        return DB::table('jobs')
            ->where('queue', $queueName)
            ->where('reserved_at', null)
            ->count();
    }
    
    private function getAverageCpuUsage($queueName)
    {
        return $this->metrics->getAverage('worker.cpu_usage', [
            'queue' => $queueName,
            'timeframe' => '5m',
        ]) ?? 0;
    }
    
    private function getAverageResponseTime($queueName)
    {
        return $this->metrics->getAverage('job.response_time', [
            'queue' => $queueName,
            'timeframe' => '5m',
        ]) ?? 0;
    }
}
```

## 📦 Job-Batching

### Erweiterte Batch-Verarbeitung

#### Batch-Job mit Fortschritts-Tracking

```php
// app/Jobs/AdvancedBatchJob.php
<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class AdvancedBatchJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $items;
    private $batchId;
    private $jobIndex;
    
    public function __construct($items, $batchId, $jobIndex)
    {
        $this->items = $items;
        $this->batchId = $batchId;
        $this->jobIndex = $jobIndex;
    }
    
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }
        
        $this->updateProgress('started');
        
        try {
            $results = [];
            $totalItems = count($this->items);
            
            foreach ($this->items as $index => $item) {
                if ($this->batch()->cancelled()) {
                    break;
                }
                
                $result = $this->processItem($item);
                $results[] = $result;
                
                // Fortschritt aktualisieren
                $progress = (($index + 1) / $totalItems) * 100;
                $this->updateProgress('processing', $progress);
            }
            
            $this->updateProgress('completed', 100, $results);
            
        } catch (\Exception $e) {
            $this->updateProgress('failed', null, null, $e->getMessage());
            throw $e;
        }
    }
    
    private function processItem($item)
    {
        // Simuliere Verarbeitung
        sleep(1);
        return strtoupper($item);
    }
    
    private function updateProgress($status, $progress = null, $results = null, $error = null)
    {
        $data = [
            'status' => $status,
            'progress' => $progress,
            'results' => $results,
            'error' => $error,
            'updated_at' => now()->toISOString(),
        ];
        
        Cache::put("batch_job_{$this->batchId}_{$this->jobIndex}", $data, 3600);
        
        // Batch-weiten Fortschritt aktualisieren
        $this->updateBatchProgress();
    }
    
    private function updateBatchProgress()
    {
        $batch = $this->batch();
        if (!$batch) return;
        
        $totalJobs = $batch->totalJobs;
        $completedJobs = $batch->processedJobs();
        $overallProgress = ($completedJobs / $totalJobs) * 100;
        
        Cache::put("batch_progress_{$this->batchId}", [
            'total_jobs' => $totalJobs,
            'completed_jobs' => $completedJobs,
            'failed_jobs' => $batch->failedJobs,
            'progress' => $overallProgress,
            'updated_at' => now()->toISOString(),
        ], 3600);
    }
}
```

#### Batch-Manager-Service

```php
// app/Services/BatchManagerService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use App\Jobs\AdvancedBatchJob;

class BatchManagerService
{
    public function createBatch($name, $items, $options = [])
    {
        $batchSize = $options['batch_size'] ?? 100;
        $chunks = array_chunk($items, $batchSize);
        $jobs = [];
        
        $batchId = uniqid('batch_', true);
        
        foreach ($chunks as $index => $chunk) {
            $jobs[] = new AdvancedBatchJob($chunk, $batchId, $index);
        }
        
        $batch = Bus::batch($jobs)
            ->name($name)
            ->then(function () use ($batchId) {
                $this->onBatchCompleted($batchId);
            })
            ->catch(function ($batch, $e) use ($batchId) {
                $this->onBatchFailed($batchId, $e);
            })
            ->finally(function () use ($batchId) {
                $this->onBatchFinished($batchId);
            })
            ->dispatch();
            
        // Batch-Metadaten speichern
        Cache::put("batch_meta_{$batchId}", [
            'id' => $batch->id,
            'name' => $name,
            'total_items' => count($items),
            'total_jobs' => count($jobs),
            'created_at' => now()->toISOString(),
            'options' => $options,
        ], 86400); // 24 Stunden
        
        return [
            'batch_id' => $batch->id,
            'internal_id' => $batchId,
            'total_jobs' => count($jobs),
            'total_items' => count($items),
        ];
    }
    
    public function getBatchStatus($batchId)
    {
        $meta = Cache::get("batch_meta_{$batchId}");
        $progress = Cache::get("batch_progress_{$batchId}");
        
        if (!$meta) {
            return null;
        }
        
        $batch = Bus::findBatch($meta['id']);
        
        return [
            'id' => $batchId,
            'name' => $meta['name'],
            'status' => $this->determineBatchStatus($batch),
            'progress' => $progress['progress'] ?? 0,
            'total_jobs' => $batch->totalJobs,
            'pending_jobs' => $batch->pendingJobs,
            'processed_jobs' => $batch->processedJobs(),
            'failed_jobs' => $batch->failedJobs,
            'created_at' => $meta['created_at'],
            'finished_at' => $batch->finishedAt?->toISOString(),
            'cancelled_at' => $batch->cancelledAt?->toISOString(),
        ];
    }
    
    public function cancelBatch($batchId)
    {
        $meta = Cache::get("batch_meta_{$batchId}");
        if (!$meta) {
            return false;
        }
        
        $batch = Bus::findBatch($meta['id']);
        if ($batch) {
            $batch->cancel();
            return true;
        }
        
        return false;
    }
    
    public function retryFailedJobs($batchId)
    {
        $meta = Cache::get("batch_meta_{$batchId}");
        if (!$meta) {
            return false;
        }
        
        $batch = Bus::findBatch($meta['id']);
        if ($batch && $batch->hasFailures()) {
            // Neue Jobs für fehlgeschlagene Items erstellen
            $failedJobIds = $batch->failedJobIds;
            // Implementation für Retry-Logik
            return true;
        }
        
        return false;
    }
    
    private function determineBatchStatus($batch)
    {
        if ($batch->cancelled()) {
            return 'cancelled';
        } elseif ($batch->hasFailures()) {
            return 'failed';
        } elseif ($batch->finished()) {
            return 'completed';
        } elseif ($batch->pendingJobs > 0) {
            return 'processing';
        } else {
            return 'pending';
        }
    }
    
    private function onBatchCompleted($batchId)
    {
        // Batch erfolgreich abgeschlossen
        Cache::put("batch_result_{$batchId}", [
            'status' => 'completed',
            'completed_at' => now()->toISOString(),
        ], 86400);
        
        // Benachrichtigungen senden
        $this->sendBatchNotification($batchId, 'completed');
    }
    
    private function onBatchFailed($batchId, $exception)
    {
        // Batch fehlgeschlagen
        Cache::put("batch_result_{$batchId}", [
            'status' => 'failed',
            'error' => $exception->getMessage(),
            'failed_at' => now()->toISOString(),
        ], 86400);
        
        // Benachrichtigungen senden
        $this->sendBatchNotification($batchId, 'failed', $exception);
    }
    
    private function onBatchFinished($batchId)
    {
        // Cleanup nach Batch-Abschluss
        $this->cleanupBatchData($batchId);
    }
    
    private function sendBatchNotification($batchId, $status, $exception = null)
    {
        // Implementierung für Benachrichtigungen
        // E-Mail, Slack, etc.
    }
    
    private function cleanupBatchData($batchId)
    {
        // Temporäre Daten nach konfigurierbarer Zeit löschen
        $cleanupDelay = config('queue-manager.batch.cleanup_delay', 86400); // 24h
        
        Cache::put("batch_cleanup_{$batchId}", true, $cleanupDelay);
    }
}
```

## 🎯 Priority-Queues

### Erweiterte Prioritäts-Verarbeitung

#### Priority-Queue-Manager

```php
// app/Services/PriorityQueueManager.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class PriorityQueueManager
{
    private $priorities = [
        'critical' => 10,
        'high' => 8,
        'normal' => 5,
        'low' => 2,
        'background' => 1,
    ];
    
    public function dispatch($job, $priority = 'normal', $delay = 0)
    {
        $priorityValue = $this->priorities[$priority] ?? 5;
        $queueName = $this->getQueueForPriority($priority);
        
        // Job mit Priorität dispatchen
        $job->onQueue($queueName)
            ->delay($delay);
            
        // Priorität in Job-Payload speichern
        $this->setPriority($job, $priorityValue);
        
        return $job->dispatch();
    }
    
    public function getNextJob($workerQueues = ['default'])
    {
        // Queues nach Priorität sortieren
        $sortedQueues = $this->sortQueuesByPriority($workerQueues);
        
        foreach ($sortedQueues as $queue) {
            $job = $this->getJobFromQueue($queue);
            if ($job) {
                return $job;
            }
        }
        
        return null;
    }
    
    private function getQueueForPriority($priority)
    {
        $queueMapping = [
            'critical' => 'critical',
            'high' => 'high-priority',
            'normal' => 'default',
            'low' => 'low-priority',
            'background' => 'background',
        ];
        
        return $queueMapping[$priority] ?? 'default';
    }
    
    private function setPriority($job, $priority)
    {
        // Priorität in Job-Metadaten speichern
        $reflection = new \ReflectionClass($job);
        if ($reflection->hasProperty('priority')) {
            $property = $reflection->getProperty('priority');
            $property->setAccessible(true);
            $property->setValue($job, $priority);
        }
    }
    
    private function sortQueuesByPriority($queues)
    {
        $queuePriorities = [];
        
        foreach ($queues as $queue) {
            $priority = $this->getQueuePriority($queue);
            $queuePriorities[$queue] = $priority;
        }
        
        // Nach Priorität sortieren (höchste zuerst)
        arsort($queuePriorities);
        
        return array_keys($queuePriorities);
    }
    
    private function getQueuePriority($queueName)
    {
        $priorityMap = [
            'critical' => 10,
            'high-priority' => 8,
            'default' => 5,
            'low-priority' => 2,
            'background' => 1,
        ];
        
        return $priorityMap[$queueName] ?? 5;
    }
    
    private function getJobFromQueue($queueName)
    {
        // Implementierung abhängig vom Queue-Driver
        if (config('queue.default') === 'redis') {
            return $this->getJobFromRedisQueue($queueName);
        } else {
            return $this->getJobFromDatabaseQueue($queueName);
        }
    }
    
    private function getJobFromRedisQueue($queueName)
    {
        $connection = Redis::connection('queue');
        $job = $connection->lpop("queues:{$queueName}");
        
        return $job ? json_decode($job, true) : null;
    }
    
    private function getJobFromDatabaseQueue($queueName)
    {
        return DB::table('jobs')
            ->where('queue', $queueName)
            ->where('reserved_at', null)
            ->where('available_at', '<=', now()->timestamp)
            ->orderBy('created_at')
            ->first();
    }
    
    public function rebalanceQueues()
    {
        // Queue-Rebalancing basierend auf aktueller Last
        $queueStats = $this->getQueueStatistics();
        
        foreach ($queueStats as $queue => $stats) {
            if ($stats['pending_jobs'] > $stats['threshold']) {
                $this->scaleUpQueue($queue);
            } elseif ($stats['pending_jobs'] < $stats['min_threshold']) {
                $this->scaleDownQueue($queue);
            }
        }
    }
    
    private function getQueueStatistics()
    {
        return DB::table('jobs')
            ->select('queue', DB::raw('COUNT(*) as pending_jobs'))
            ->where('reserved_at', null)
            ->groupBy('queue')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->queue => [
                    'pending_jobs' => $item->pending_jobs,
                    'threshold' => $this->getThresholdForQueue($item->queue),
                    'min_threshold' => $this->getMinThresholdForQueue($item->queue),
                ]];
            })
            ->toArray();
    }
    
    private function getThresholdForQueue($queueName)
    {
        $thresholds = [
            'critical' => 5,
            'high-priority' => 20,
            'default' => 50,
            'low-priority' => 100,
            'background' => 200,
        ];
        
        return $thresholds[$queueName] ?? 50;
    }
    
    private function getMinThresholdForQueue($queueName)
    {
        return intval($this->getThresholdForQueue($queueName) * 0.2);
    }
    
    private function scaleUpQueue($queueName)
    {
        // Auto-Scaling-Service aufrufen
        app(AutoScalingService::class)->scaleUpQueue($queueName);
    }
    
    private function scaleDownQueue($queueName)
    {
        // Auto-Scaling-Service aufrufen
        app(AutoScalingService::class)->scaleDownQueue($queueName);
    }
}
```

## 🚦 Rate-Limiting

### Erweiterte Rate-Limiting-Strategien

#### Rate-Limiter-Service

```php
// app/Services/RateLimiterService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;

class RateLimiterService
{
    private $algorithms = [
        'token_bucket' => TokenBucketLimiter::class,
        'sliding_window' => SlidingWindowLimiter::class,
        'fixed_window' => FixedWindowLimiter::class,
        'leaky_bucket' => LeakyBucketLimiter::class,
    ];
    
    public function checkLimit($key, $maxRequests, $windowSize, $algorithm = 'token_bucket')
    {
        $limiterClass = $this->algorithms[$algorithm] ?? $this->algorithms['token_bucket'];
        $limiter = new $limiterClass($key, $maxRequests, $windowSize);
        
        return $limiter->attempt();
    }
    
    public function getRemainingAttempts($key, $maxRequests, $windowSize, $algorithm = 'token_bucket')
    {
        $limiterClass = $this->algorithms[$algorithm];
        $limiter = new $limiterClass($key, $maxRequests, $windowSize);
        
        return $limiter->remaining();
    }
    
    public function resetLimit($key)
    {
        Redis::del($key);
        Cache::forget($key);
    }
}

// Token-Bucket-Implementierung
class TokenBucketLimiter
{
    private $key;
    private $capacity;
    private $refillRate;
    private $redis;
    
    public function __construct($key, $capacity, $refillPeriod)
    {
        $this->key = "rate_limit:token_bucket:{$key}";
        $this->capacity = $capacity;
        $this->refillRate = $capacity / $refillPeriod; // Tokens pro Sekunde
        $this->redis = Redis::connection();
    }
    
    public function attempt($tokens = 1)
    {
        $now = microtime(true);
        $bucket = $this->getBucket();
        
        // Tokens nachfüllen
        $timePassed = $now - $bucket['last_refill'];
        $tokensToAdd = $timePassed * $this->refillRate;
        $bucket['tokens'] = min($this->capacity, $bucket['tokens'] + $tokensToAdd);
        $bucket['last_refill'] = $now;
        
        // Prüfen ob genug Tokens vorhanden
        if ($bucket['tokens'] >= $tokens) {
            $bucket['tokens'] -= $tokens;
            $this->saveBucket($bucket);
            return true;
        }
        
        $this->saveBucket($bucket);
        return false;
    }
    
    public function remaining()
    {
        $bucket = $this->getBucket();
        return floor($bucket['tokens']);
    }
    
    private function getBucket()
    {
        $data = $this->redis->get($this->key);
        
        if ($data) {
            return json_decode($data, true);
        }
        
        return [
            'tokens' => $this->capacity,
            'last_refill' => microtime(true),
        ];
    }
    
    private function saveBucket($bucket)
    {
        $this->redis->setex($this->key, 3600, json_encode($bucket));
    }
}

// Sliding-Window-Implementierung
class SlidingWindowLimiter
{
    private $key;
    private $maxRequests;
    private $windowSize;
    private $redis;
    
    public function __construct($key, $maxRequests, $windowSize)
    {
        $this->key = "rate_limit:sliding_window:{$key}";
        $this->maxRequests = $maxRequests;
        $this->windowSize = $windowSize;
        $this->redis = Redis::connection();
    }
    
    public function attempt()
    {
        $now = microtime(true);
        $windowStart = $now - $this->windowSize;
        
        // Alte Einträge entfernen
        $this->redis->zremrangebyscore($this->key, 0, $windowStart);
        
        // Aktuelle Anzahl prüfen
        $currentCount = $this->redis->zcard($this->key);
        
        if ($currentCount < $this->maxRequests) {
            // Request hinzufügen
            $this->redis->zadd($this->key, $now, uniqid());
            $this->redis->expire($this->key, $this->windowSize + 1);
            return true;
        }
        
        return false;
    }
    
    public function remaining()
    {
        $now = microtime(true);
        $windowStart = $now - $this->windowSize;
        
        $this->redis->zremrangebyscore($this->key, 0, $windowStart);
        $currentCount = $this->redis->zcard($this->key);
        
        return max(0, $this->maxRequests - $currentCount);
    }
}
```

## 🔌 Circuit