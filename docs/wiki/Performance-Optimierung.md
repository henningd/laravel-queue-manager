# Performance Optimierung

Diese umfassende Anleitung zeigt dir, wie du die Performance des Laravel Queue Manager Systems maximierst. Du lernst bewährte Optimierungsstrategien und -techniken kennen.

## 🎯 Übersicht

Performance-Optimierung umfasst:

1. **System-Level-Optimierung** - Server und Infrastruktur
2. **Database-Optimierung** - Queries und Indizes
3. **Queue-Optimierung** - Worker und Job-Konfiguration
4. **Memory-Optimierung** - Speicher-Management
5. **Caching-Strategien** - Intelligente Zwischenspeicherung
6. **Network-Optimierung** - Verbindungen und Latenz
7. **Code-Optimierung** - Algorithmen und Datenstrukturen
8. **Monitoring und Profiling** - Kontinuierliche Überwachung

## 🖥️ System-Level-Optimierung

### Server-Konfiguration

#### PHP-Optimierung

```ini
; php.ini Optimierungen
memory_limit = 512M
max_execution_time = 300
max_input_time = 300

; OPcache aktivieren
opcache.enable = 1
opcache.enable_cli = 1
opcache.memory_consumption = 256
opcache.interned_strings_buffer = 16
opcache.max_accelerated_files = 20000
opcache.validate_timestamps = 0
opcache.save_comments = 0
opcache.fast_shutdown = 1

; Realpath Cache
realpath_cache_size = 4096K
realpath_cache_ttl = 600

; Session-Optimierung
session.save_handler = redis
session.save_path = "tcp://127.0.0.1:6379"

; Garbage Collection
zend.enable_gc = 1
```

#### Nginx-Konfiguration

```nginx
# /etc/nginx/sites-available/queue-manager
server {
    listen 80;
    server_name queue-manager.example.com;
    root /var/www/queue-manager/public;
    index index.php;

    # Gzip-Kompression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript 
               application/javascript application/xml+rss 
               application/json application/xml;

    # Browser-Caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # PHP-FPM-Optimierung
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Performance-Tuning
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_read_timeout 300;
    }

    # Queue-Manager spezifische Optimierungen
    location /queue-manager/api/ {
        # API-Caching
        add_header Cache-Control "no-cache, must-revalidate";
        
        # Rate-Limiting
        limit_req zone=api burst=20 nodelay;
    }
}

# Rate-Limiting-Zone
http {
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
}
```

#### PHP-FPM-Optimierung

```ini
; /etc/php/8.2/fpm/pool.d/queue-manager.conf
[queue-manager]
user = www-data
group = www-data

listen = /var/run/php/php8.2-fpm-queue-manager.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

; Process-Management
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 15
pm.max_requests = 1000

; Performance-Tuning
request_terminate_timeout = 300
request_slowlog_timeout = 10
slowlog = /var/log/php-fpm-slow.log

; Memory-Limits
php_admin_value[memory_limit] = 512M
php_admin_value[max_execution_time] = 300

; OPcache für Pool
php_admin_value[opcache.enable] = 1
php_admin_value[opcache.memory_consumption] = 256
```

### Supervisor-Konfiguration für Worker

```ini
; /etc/supervisor/conf.d/queue-manager-workers.conf
[program:queue-manager-default]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/queue-manager/artisan queue:work --queue=default --sleep=3 --tries=3 --max-time=3600
directory=/var/www/queue-manager
autostart=true
autorestart=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/supervisor/queue-manager-default.log
stopwaitsecs=3600

[program:queue-manager-high-priority]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/queue-manager/artisan queue:work --queue=high-priority --sleep=1 --tries=3 --max-time=1800
directory=/var/www/queue-manager
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/queue-manager-high-priority.log
stopwaitsecs=3600

[group:queue-manager]
programs=queue-manager-default,queue-manager-high-priority
priority=999
```

## 🗄️ Database-Optimierung

### MySQL-Optimierung

#### Konfiguration

```ini
# /etc/mysql/mysql.conf.d/queue-manager.cnf
[mysqld]
# InnoDB-Optimierung
innodb_buffer_pool_size = 2G
innodb_log_file_size = 512M
innodb_log_buffer_size = 64M
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Query-Cache
query_cache_type = 1
query_cache_size = 256M
query_cache_limit = 2M

# Connection-Optimierung
max_connections = 200
max_connect_errors = 10000
connect_timeout = 10
wait_timeout = 600
interactive_timeout = 600

# Table-Cache
table_open_cache = 4000
table_definition_cache = 2000

# Temp-Tables
tmp_table_size = 256M
max_heap_table_size = 256M

# Slow-Query-Log
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
log_queries_not_using_indexes = 1
```

#### Indizes optimieren

```sql
-- Queue-Jobs-Tabelle optimieren
ALTER TABLE jobs 
ADD INDEX idx_queue_available_at (queue, available_at),
ADD INDEX idx_reserved_at (reserved_at),
ADD INDEX idx_created_at (created_at);

-- Failed-Jobs-Tabelle optimieren
ALTER TABLE failed_jobs 
ADD INDEX idx_queue_failed_at (queue, failed_at),
ADD INDEX idx_connection (connection);

-- Queue-Workers-Tabelle optimieren
ALTER TABLE queue_workers 
ADD INDEX idx_queue_status (queue, status),
ADD INDEX idx_last_seen (last_seen_at),
ADD INDEX idx_created_at (created_at);

-- Queue-Configurations-Tabelle optimieren
ALTER TABLE queue_configurations 
ADD INDEX idx_name_active (name, is_active),
ADD INDEX idx_priority (priority);

-- Composite-Index für häufige Queries
ALTER TABLE jobs 
ADD INDEX idx_queue_status_available (queue, reserved_at, available_at);
```

#### Query-Optimierung

```php
// app/Services/OptimizedQueueService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class OptimizedQueueService
{
    public function getQueueStatistics($useCache = true)
    {
        $cacheKey = 'queue_statistics';
        $cacheTtl = 60; // 1 Minute
        
        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        
        // Optimierte Query mit Subqueries
        $stats = DB::select("
            SELECT 
                q.name as queue_name,
                COALESCE(pending.count, 0) as pending_jobs,
                COALESCE(failed.count, 0) as failed_jobs,
                COALESCE(workers.count, 0) as active_workers,
                q.priority,
                q.max_jobs_per_minute
            FROM queue_configurations q
            LEFT JOIN (
                SELECT queue, COUNT(*) as count 
                FROM jobs 
                WHERE reserved_at IS NULL 
                GROUP BY queue
            ) pending ON q.name = pending.queue
            LEFT JOIN (
                SELECT queue, COUNT(*) as count 
                FROM failed_jobs 
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
                GROUP BY queue
            ) failed ON q.name = failed.queue
            LEFT JOIN (
                SELECT queue, COUNT(*) as count 
                FROM queue_workers 
                WHERE status = 'running' 
                AND last_seen_at >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
                GROUP BY queue
            ) workers ON q.name = workers.queue
            WHERE q.is_active = 1
            ORDER BY q.priority DESC, q.name
        ");
        
        $result = collect($stats)->keyBy('queue_name');
        
        if ($useCache) {
            Cache::put($cacheKey, $result, $cacheTtl);
        }
        
        return $result;
    }
    
    public function getJobThroughput($queue = null, $hours = 24)
    {
        $cacheKey = "job_throughput_{$queue}_{$hours}h";
        
        return Cache::remember($cacheKey, 300, function () use ($queue, $hours) {
            $query = DB::table('jobs_history')
                ->select(
                    DB::raw('HOUR(completed_at) as hour'),
                    DB::raw('COUNT(*) as job_count'),
                    DB::raw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration')
                )
                ->where('completed_at', '>=', now()->subHours($hours))
                ->where('status', 'completed');
                
            if ($queue) {
                $query->where('queue', $queue);
            }
            
            return $query
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();
        });
    }
    
    public function getSlowJobs($limit = 10, $minDuration = 30)
    {
        return Cache::remember('slow_jobs', 300, function () use ($limit, $minDuration) {
            return DB::table('jobs_history')
                ->select(
                    'job_class',
                    'queue',
                    DB::raw('AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration'),
                    DB::raw('MAX(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as max_duration'),
                    DB::raw('COUNT(*) as job_count')
                )
                ->where('completed_at', '>=', now()->subDay())
                ->where('status', 'completed')
                ->havingRaw('avg_duration >= ?', [$minDuration])
                ->groupBy('job_class', 'queue')
                ->orderBy('avg_duration', 'desc')
                ->limit($limit)
                ->get();
        });
    }
}
```

### Redis-Optimierung

#### Redis-Konfiguration

```conf
# /etc/redis/redis.conf

# Memory-Optimierung
maxmemory 2gb
maxmemory-policy allkeys-lru

# Persistence-Optimierung
save 900 1
save 300 10
save 60 10000

# AOF-Konfiguration
appendonly yes
appendfsync everysec
no-appendfsync-on-rewrite yes
auto-aof-rewrite-percentage 100
auto-aof-rewrite-min-size 64mb

# Network-Optimierung
tcp-keepalive 300
timeout 0

# Performance-Tuning
hash-max-ziplist-entries 512
hash-max-ziplist-value 64
list-max-ziplist-size -2
set-max-intset-entries 512
zset-max-ziplist-entries 128
zset-max-ziplist-value 64
```

#### Redis-Queue-Optimierung

```php
// config/queue.php
'redis' => [
    'driver' => 'redis',
    'connection' => 'queue',
    'queue' => env('REDIS_QUEUE', 'default'),
    'retry_after' => 90,
    'block_for' => null,
    'after_commit' => false,
    
    // Performance-Optimierungen
    'options' => [
        'serializer' => 'igbinary', // Schnellere Serialisierung
        'compression' => 'lz4',     // Kompression aktivieren
    ],
],

// Redis-Verbindung optimieren
'redis' => [
    'queue' => [
        'host' => env('REDIS_HOST', '127.0.0.1'),
        'password' => env('REDIS_PASSWORD', null),
        'port' => env('REDIS_PORT', 6379),
        'database' => env('REDIS_QUEUE_DB', 1),
        
        // Connection-Pool
        'options' => [
            'prefix' => env('REDIS_PREFIX', 'queue:'),
            'serializer' => 'igbinary',
            'compression' => 'lz4',
            
            // Persistent Connections
            'persistent' => true,
            'read_timeout' => 60,
            'timeout' => 5,
        ],
    ],
],
```

## ⚡ Queue-Optimierung

### Worker-Konfiguration optimieren

#### Adaptive Worker-Skalierung

```php
// app/Services/WorkerScalingService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use HenningD\LaravelQueueManager\Models\QueueWorker;

class WorkerScalingService
{
    private $scalingRules = [
        'default' => [
            'min_workers' => 1,
            'max_workers' => 10,
            'scale_up_threshold' => 20,
            'scale_down_threshold' => 5,
            'scale_factor' => 2,
        ],
        'high-priority' => [
            'min_workers' => 2,
            'max_workers' => 8,
            'scale_up_threshold' => 10,
            'scale_down_threshold' => 2,
            'scale_factor' => 1.5,
        ],
    ];
    
    public function autoScale()
    {
        foreach ($this->scalingRules as $queue => $rules) {
            $this->scaleQueue($queue, $rules);
        }
    }
    
    private function scaleQueue($queue, $rules)
    {
        $queueSize = $this->getQueueSize($queue);
        $activeWorkers = $this->getActiveWorkers($queue);
        
        if ($queueSize >= $rules['scale_up_threshold'] && 
            $activeWorkers < $rules['max_workers']) {
            
            $newWorkers = min(
                ceil($activeWorkers * $rules['scale_factor']),
                $rules['max_workers']
            );
            
            $this->scaleUp($queue, $newWorkers - $activeWorkers);
            
        } elseif ($queueSize <= $rules['scale_down_threshold'] && 
                  $activeWorkers > $rules['min_workers']) {
            
            $newWorkers = max(
                floor($activeWorkers / $rules['scale_factor']),
                $rules['min_workers']
            );
            
            $this->scaleDown($queue, $activeWorkers - $newWorkers);
        }
    }
    
    private function getQueueSize($queue)
    {
        return DB::table('jobs')
            ->where('queue', $queue)
            ->where('reserved_at', null)
            ->count();
    }
    
    private function getActiveWorkers($queue)
    {
        return QueueWorker::where('queue', $queue)
            ->where('status', 'running')
            ->where('last_seen_at', '>=', now()->subMinutes(5))
            ->count();
    }
    
    private function scaleUp($queue, $count)
    {
        for ($i = 0; $i < $count; $i++) {
            QueueWorker::create([
                'name' => "auto-worker-{$queue}-" . uniqid(),
                'queue' => $queue,
                'timeout' => $this->getOptimalTimeout($queue),
                'memory' => $this->getOptimalMemory($queue),
                'sleep' => $this->getOptimalSleep($queue),
                'auto_start' => true,
            ]);
        }
        
        \Log::info("Scaled up {$queue} queue by {$count} workers");
    }
    
    private function scaleDown($queue, $count)
    {
        $workers = QueueWorker::where('queue', $queue)
            ->where('status', 'running')
            ->where('name', 'like', 'auto-worker-%')
            ->limit($count)
            ->get();
            
        foreach ($workers as $worker) {
            $worker->stop();
        }
        
        \Log::info("Scaled down {$queue} queue by {$count} workers");
    }
    
    private function getOptimalTimeout($queue)
    {
        // Basierend auf historischen Daten
        $avgDuration = DB::table('jobs_history')
            ->where('queue', $queue)
            ->where('completed_at', '>=', now()->subDay())
            ->avg(DB::raw('TIMESTAMPDIFF(SECOND, started_at, completed_at)'));
            
        return max(60, ceil($avgDuration * 2)); // 2x der durchschnittlichen Dauer
    }
    
    private function getOptimalMemory($queue)
    {
        // Basierend auf Job-Typ
        $memoryMap = [
            'emails' => 128,
            'image-processing' => 512,
            'data-export' => 256,
            'default' => 128,
        ];
        
        return $memoryMap[$queue] ?? $memoryMap['default'];
    }
    
    private function getOptimalSleep($queue)
    {
        $queueSize = $this->getQueueSize($queue);
        
        // Dynamische Sleep-Zeit basierend auf Queue-Größe
        if ($queueSize > 100) return 1;      // Hohe Last
        if ($queueSize > 50) return 2;       // Mittlere Last
        if ($queueSize > 10) return 3;       // Normale Last
        return 5;                            // Niedrige Last
    }
}
```

#### Cron-Job für Auto-Scaling

```bash
# /etc/cron.d/queue-manager-scaling
# Auto-Scaling alle 2 Minuten
*/2 * * * * www-data cd /var/www/queue-manager && php artisan queue:auto-scale

# Performance-Monitoring alle 5 Minuten
*/5 * * * * www-data cd /var/www/queue-manager && php artisan queue:performance-check

# Cleanup alle Stunde
0 * * * * www-data cd /var/www/queue-manager && php artisan queue:cleanup
```

### Job-Optimierung

#### Batch-Processing

```php
// app/Jobs/OptimizedBatchJob.php
<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class OptimizedBatchJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $items;
    private $batchSize;
    
    public function __construct($items, $batchSize = 1000)
    {
        $this->items = $items;
        $this->batchSize = $batchSize;
    }
    
    public function handle()
    {
        if ($this->batch()->cancelled()) {
            return;
        }
        
        // Chunked Processing für Memory-Effizienz
        collect($this->items)->chunk($this->batchSize)->each(function ($chunk) {
            if ($this->batch()->cancelled()) {
                return false;
            }
            
            $this->processChunk($chunk);
            
            // Memory explizit freigeben
            unset($chunk);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        });
    }
    
    private function processChunk($items)
    {
        // Bulk-Insert für bessere Performance
        $data = $items->map(function ($item) {
            return [
                'processed_data' => $this->processItem($item),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        })->toArray();
        
        DB::table('processed_items')->insert($data);
    }
    
    private function processItem($item)
    {
        // Item-spezifische Verarbeitung
        return strtoupper($item);
    }
}
```

#### Lazy Loading und Eager Loading

```php
// app/Jobs/OptimizedDataJob.php
<?php

namespace App\Jobs;

class OptimizedDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    private $userIds;
    
    public function __construct($userIds)
    {
        // Nur IDs speichern, nicht ganze Models
        $this->userIds = $userIds;
    }
    
    public function handle()
    {
        // Eager Loading für bessere Performance
        $users = User::with(['profile', 'orders.items'])
            ->whereIn('id', $this->userIds)
            ->get();
            
        // Chunked Processing
        $users->chunk(100)->each(function ($userChunk) {
            $this->processUsers($userChunk);
        });
    }
    
    private function processUsers($users)
    {
        // Bulk-Operations verwenden
        $updates = [];
        
        foreach ($users as $user) {
            $updates[] = [
                'id' => $user->id,
                'processed_at' => now(),
                'status' => 'completed',
            ];
        }
        
        // Bulk-Update
        DB::table('users')->upsert($updates, ['id'], ['processed_at', 'status']);
    }
}
```

## 🧠 Memory-Optimierung

### Memory-Pool-Management

```php
// app/Services/MemoryPoolService.php
<?php

namespace App\Services;

class MemoryPoolService
{
    private static $pools = [];
    private static $maxPoolSize = 1000;
    
    public static function getObject($class, ...$args)
    {
        $poolKey = $class;
        
        if (!isset(self::$pools[$poolKey])) {
            self::$pools[$poolKey] = [];
        }
        
        if (!empty(self::$pools[$poolKey])) {
            $object = array_pop(self::$pools[$poolKey]);
            $object->reset(...$args);
            return $object;
        }
        
        return new $class(...$args);
    }
    
    public static function returnObject($object)
    {
        $class = get_class($object);
        
        if (!isset(self::$pools[$class])) {
            self::$pools[$class] = [];
        }
        
        if (count(self::$pools[$class]) < self::$maxPoolSize) {
            self::$pools[$class][] = $object;
        }
    }
    
    public static function clearPools()
    {
        self::$pools = [];
        
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }
    
    public static function getMemoryUsage()
    {
        $usage = [];
        
        foreach (self::$pools as $class => $pool) {
            $usage[$class] = [
                'count' => count($pool),
                'estimated_memory' => count($pool) * 1024, // Grobe Schätzung
            ];
        }
        
        return $usage;
    }
}
```

### Memory-optimierte Job-Base-Klasse

```php
// app/Jobs/MemoryOptimizedJob.php
<?php

namespace App\Jobs;

use App\Services\MemoryPoolService;

abstract class MemoryOptimizedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $memoryLimit;
    protected $memoryCheckInterval = 100; // Alle 100 Operationen prüfen
    protected $operationCount = 0;
    
    public function __construct()
    {
        $this->memoryLimit = $this->getMemoryLimit();
    }
    
    public function handle()
    {
        try {
            $this->execute();
        } finally {
            $this->cleanup();
        }
    }
    
    abstract protected function execute();
    
    protected function checkMemoryUsage()
    {
        $this->operationCount++;
        
        if ($this->operationCount % $this->memoryCheckInterval === 0) {
            $currentUsage = memory_get_usage(true);
            $usagePercent = ($currentUsage / $this->memoryLimit) * 100;
            
            if ($usagePercent > 80) {
                $this->forceGarbageCollection();
                
                // Nach GC nochmal prüfen
                $currentUsage = memory_get_usage(true);
                $usagePercent = ($currentUsage / $this->memoryLimit) * 100;
                
                if ($usagePercent > 90) {
                    throw new \RuntimeException('Memory limit exceeded');
                }
            }
        }
    }
    
    protected function forceGarbageCollection()
    {
        // Object-Pools leeren
        MemoryPoolService::clearPools();
        
        // PHP Garbage Collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
        
        // Memory-Defragmentierung (falls verfügbar)
        if (function_exists('gc_mem_caches')) {
            gc_mem_caches();
        }
    }
    
    protected function cleanup()
    {
        // Explizite Cleanup-Logik
        $this->forceGarbageCollection();
    }
    
    private function getMemoryLimit()
    {
        $limit = ini_get('memory_limit');
        
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }
        
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit)-1]);
        $limit = (int) $limit;
        
        switch($last) {
            case 'g': $limit *= 1024;
            case 'm': $limit *= 1024;
            case 'k': $limit *= 1024;
        }
        
        return $limit;
    }
}
```

## 🚀 Caching-Strategien

### Multi-Level-Caching

```php
// app/Services/MultiLevelCacheService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class MultiLevelCacheService
{
    private $l1Cache = []; // In-Memory Cache
    private $l1MaxSize = 1000;
    private $l2Ttl = 300; // Redis Cache TTL
    private $l3Ttl = 3600; // Database Cache TTL
    
    public function get($key, $callback = null)
    {
        // Level 1: In-Memory Cache
        if (isset($this->l1Cache[$key])) {
            return $this->l1Cache[$key];
        }
        
        // Level 2: Redis Cache
        $l2Key = "l2:{$key}";
        $value = Redis::get($l2Key);
        
        if ($value !== null) {
            $value = unserialize($value);
            $this->setL1Cache($key, $value);
            return $value;
        }
        
        // Level 3: Database/Callback
        if ($callback) {
            $value = $callback();
            $this->put($key, $value);
            return $value;
        }
        
        return null;
    }
    
    public function put($key, $value, $ttl = null)
    {
        // Level 1: In-Memory
        $this->setL1Cache($key, $value);
        
        // Level 2: Redis
        $l2Key = "l2:{$key}";
        Redis::setex($l2Key, $ttl ?? $this->l2Ttl, serialize($value));
        
        // Level 3: Laravel Cache (Database)
        Cache::put($key, $value, $ttl ?? $this->l3Ttl);
    }
    
    public function forget($key)
    {
        // Alle Levels löschen
        unset($this->l1Cache[$key]);
        Redis::del("l2:{$key}");
        Cache::forget($key);
    }
    
    private function setL1Cache($key, $value)
    {
        if (count($this->l1Cache) >= $this->l1MaxSize) {
            // LRU-Eviction
            $firstKey = array_key_first($this->l1Cache);
            unset($this->l1Cache[$firstKey]);
        }
        
        $this->l1Cache[$key] = $value;
    }
    
    public function getStats()
    {
        return [
            'l1_size' => count($this->l1Cache),
            'l1_max_size' => $this->l1MaxSize,
            'l2_ttl' => $this->l