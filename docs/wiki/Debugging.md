# Debugging

Diese umfassende Anleitung zeigt dir, wie du Probleme im Laravel Queue Manager systematisch diagnostizierst und behebst. Du lernst professionelle Debugging-Techniken und Tools kennen.

## 🎯 Übersicht

Debugging-Strategien umfassen:

1. **Systematische Problemanalyse** - Strukturierte Herangehensweise
2. **Logging und Monitoring** - Detaillierte Protokollierung
3. **Performance-Debugging** - Engpässe identifizieren
4. **Memory-Debugging** - Speicher-Probleme lösen
5. **Network-Debugging** - Verbindungsprobleme diagnostizieren
6. **Database-Debugging** - Datenbankprobleme analysieren
7. **Worker-Debugging** - Worker-spezifische Probleme
8. **Job-Debugging** - Einzelne Jobs analysieren

## 🔍 Systematische Problemanalyse

### Debugging-Workflow

#### 1. Problem identifizieren

```bash
# Schritt 1: Symptome sammeln
echo "=== PROBLEM ANALYSIS ==="
echo "Zeitpunkt: $(date)"
echo "Symptome:"
echo "- Jobs bleiben hängen"
echo "- Hohe Memory-Nutzung"
echo "- Langsame Verarbeitung"
echo ""

# Schritt 2: System-Status prüfen
php artisan queue-manager:status
php artisan queue:monitor
```

#### 2. Logs analysieren

```bash
# Laravel Logs
tail -f storage/logs/laravel.log | grep -i queue

# System Logs (Linux)
tail -f /var/log/syslog | grep php

# Worker Logs
ps aux | grep "queue:work"
```

#### 3. Metriken sammeln

```php
// Debug-Helper erstellen
// app/Helpers/QueueDebugger.php
<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class QueueDebugger
{
    public static function captureSnapshot()
    {
        $snapshot = [
            'timestamp' => now()->toISOString(),
            'system' => self::getSystemMetrics(),
            'queues' => self::getQueueMetrics(),
            'workers' => self::getWorkerMetrics(),
            'database' => self::getDatabaseMetrics(),
            'memory' => self::getMemoryMetrics(),
        ];
        
        Log::info('Queue Debug Snapshot', $snapshot);
        
        return $snapshot;
    }
    
    private static function getSystemMetrics()
    {
        return [
            'php_version' => PHP_VERSION,
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'current_memory' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'load_average' => sys_getloadavg(),
        ];
    }
    
    private static function getQueueMetrics()
    {
        return [
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'queue_sizes' => DB::table('jobs')
                ->select('queue', DB::raw('COUNT(*) as count'))
                ->groupBy('queue')
                ->get()
                ->pluck('count', 'queue')
                ->toArray(),
        ];
    }
    
    private static function getWorkerMetrics()
    {
        $workers = [];
        
        // Aktive Worker-Prozesse finden
        $processes = shell_exec('ps aux | grep "queue:work" | grep -v grep');
        if ($processes) {
            $lines = explode("\n", trim($processes));
            foreach ($lines as $line) {
                if (preg_match('/\s+(\d+)\s+/', $line, $matches)) {
                    $pid = $matches[1];
                    $workers[] = [
                        'pid' => $pid,
                        'memory' => self::getProcessMemory($pid),
                        'cpu' => self::getProcessCpu($pid),
                        'runtime' => self::getProcessRuntime($pid),
                    ];
                }
            }
        }
        
        return $workers;
    }
    
    private static function getDatabaseMetrics()
    {
        return [
            'connections' => DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 'N/A',
            'slow_queries' => DB::select('SHOW STATUS LIKE "Slow_queries"')[0]->Value ?? 'N/A',
            'table_locks' => DB::select('SHOW STATUS LIKE "Table_locks_waited"')[0]->Value ?? 'N/A',
        ];
    }
    
    private static function getMemoryMetrics()
    {
        return [
            'current_usage' => memory_get_usage(true),
            'peak_usage' => memory_get_peak_usage(true),
            'limit' => self::parseMemoryLimit(ini_get('memory_limit')),
            'usage_percentage' => (memory_get_usage(true) / self::parseMemoryLimit(ini_get('memory_limit'))) * 100,
        ];
    }
    
    private static function parseMemoryLimit($limit)
    {
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

### Debug-Commands erstellen

```php
// app/Console/Commands/QueueDebugCommand.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Helpers\QueueDebugger;

class QueueDebugCommand extends Command
{
    protected $signature = 'queue:debug {action} {--queue=} {--worker=} {--verbose}';
    protected $description = 'Debug Queue-Probleme';

    public function handle()
    {
        $action = $this->argument('action');
        
        switch ($action) {
            case 'snapshot':
                $this->takeSnapshot();
                break;
            case 'analyze':
                $this->analyzeProblems();
                break;
            case 'trace':
                $this->traceJobs();
                break;
            case 'monitor':
                $this->monitorRealtime();
                break;
            default:
                $this->error("Unbekannte Aktion: {$action}");
        }
    }
    
    private function takeSnapshot()
    {
        $this->info('Erstelle Debug-Snapshot...');
        
        $snapshot = QueueDebugger::captureSnapshot();
        
        $this->table(['Metrik', 'Wert'], [
            ['PHP Memory Usage', $this->formatBytes($snapshot['memory']['current_usage'])],
            ['Peak Memory', $this->formatBytes($snapshot['memory']['peak_usage'])],
            ['Memory Limit', $this->formatBytes($snapshot['memory']['limit'])],
            ['Usage %', round($snapshot['memory']['usage_percentage'], 2) . '%'],
            ['Pending Jobs', $snapshot['queues']['pending_jobs']],
            ['Failed Jobs', $snapshot['queues']['failed_jobs']],
            ['Active Workers', count($snapshot['workers'])],
        ]);
        
        if ($this->option('verbose')) {
            $this->info('Detaillierte Informationen:');
            $this->line(json_encode($snapshot, JSON_PRETTY_PRINT));
        }
    }
    
    private function analyzeProblems()
    {
        $this->info('Analysiere Queue-Probleme...');
        
        $problems = [];
        
        // Memory-Probleme
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        if (($memoryUsage / $memoryLimit) > 0.8) {
            $problems[] = [
                'type' => 'Memory',
                'severity' => 'High',
                'description' => 'Memory-Nutzung über 80%',
                'suggestion' => 'Memory-Limit erhöhen oder Jobs optimieren',
            ];
        }
        
        // Hängende Jobs
        $oldJobs = DB::table('jobs')
            ->where('created_at', '<', now()->subMinutes(30))
            ->count();
        if ($oldJobs > 0) {
            $problems[] = [
                'type' => 'Stuck Jobs',
                'severity' => 'Medium',
                'description' => "{$oldJobs} Jobs älter als 30 Minuten",
                'suggestion' => 'Worker neustarten oder Jobs manuell verarbeiten',
            ];
        }
        
        // Hohe Fehlerrate
        $totalJobs = DB::table('failed_jobs')->count() + DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        if ($totalJobs > 0 && ($failedJobs / $totalJobs) > 0.1) {
            $problems[] = [
                'type' => 'High Failure Rate',
                'severity' => 'High',
                'description' => 'Fehlerrate über 10%',
                'suggestion' => 'Job-Code überprüfen und Fehlerbehandlung verbessern',
            ];
        }
        
        if (empty($problems)) {
            $this->info('✅ Keine Probleme gefunden');
        } else {
            $this->table(['Typ', 'Schwere', 'Beschreibung', 'Empfehlung'], $problems);
        }
    }
    
    private function traceJobs()
    {
        $queue = $this->option('queue') ?? 'default';
        
        $this->info("Trace Jobs in Queue: {$queue}");
        
        // Aktiviere detailliertes Logging
        config(['queue-manager.debugging.trace_jobs' => true]);
        
        $this->info('Job-Tracing aktiviert. Überwache Logs...');
        
        // Tail Laravel Log
        $this->line('Drücke Ctrl+C zum Beenden');
        passthru('tail -f storage/logs/laravel.log | grep -i "job\|queue"');
    }
    
    private function monitorRealtime()
    {
        $this->info('Real-time Queue-Monitoring gestartet...');
        
        while (true) {
            $this->line("\033[2J\033[H"); // Clear screen
            $this->info('Queue Status - ' . now()->format('H:i:s'));
            
            $snapshot = QueueDebugger::captureSnapshot();
            
            $this->table(['Queue', 'Pending'], 
                collect($snapshot['queues']['queue_sizes'])
                    ->map(fn($count, $queue) => [$queue, $count])
                    ->values()
                    ->toArray()
            );
            
            $this->line('Memory: ' . $this->formatBytes($snapshot['memory']['current_usage']) . 
                       ' / ' . $this->formatBytes($snapshot['memory']['limit']) . 
                       ' (' . round($snapshot['memory']['usage_percentage'], 1) . '%)');
            
            $this->line('Workers: ' . count($snapshot['workers']));
            
            sleep(2);
        }
    }
    
    private function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
```

## 📊 Logging und Monitoring

### Erweiterte Logging-Konfiguration

#### Custom Log-Channel

```php
// config/logging.php
'channels' => [
    'queue-debug' => [
        'driver' => 'daily',
        'path' => storage_path('logs/queue-debug.log'),
        'level' => 'debug',
        'days' => 14,
        'formatter' => \App\Logging\QueueDebugFormatter::class,
    ],
],
```

#### Custom Log-Formatter

```php
// app/Logging/QueueDebugFormatter.php
<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class QueueDebugFormatter extends LineFormatter
{
    public function format(array $record): string
    {
        $output = sprintf(
            "[%s] %s.%s: %s\n",
            $record['datetime']->format('Y-m-d H:i:s'),
            $record['level_name'],
            $record['channel'],
            $record['message']
        );
        
        if (!empty($record['context'])) {
            $output .= "Context: " . json_encode($record['context'], JSON_PRETTY_PRINT) . "\n";
        }
        
        if (!empty($record['extra'])) {
            $output .= "Extra: " . json_encode($record['extra'], JSON_PRETTY_PRINT) . "\n";
        }
        
        return $output;
    }
}
```

#### Job-Tracing aktivieren

```php
// app/Jobs/TraceableJob.php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

abstract class TraceableJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $traceId;
    protected $startTime;
    
    public function __construct()
    {
        $this->traceId = uniqid('job_', true);
        $this->startTime = microtime(true);
    }
    
    public function handle()
    {
        $this->logJobStart();
        
        try {
            $result = $this->execute();
            $this->logJobSuccess($result);
            return $result;
        } catch (\Exception $e) {
            $this->logJobFailure($e);
            throw $e;
        }
    }
    
    abstract protected function execute();
    
    protected function logJobStart()
    {
        Log::channel('queue-debug')->info('Job Started', [
            'trace_id' => $this->traceId,
            'job_class' => static::class,
            'queue' => $this->queue,
            'attempts' => $this->attempts(),
            'memory_before' => memory_get_usage(true),
            'timestamp' => now()->toISOString(),
        ]);
    }
    
    protected function logJobSuccess($result = null)
    {
        $duration = microtime(true) - $this->startTime;
        
        Log::channel('queue-debug')->info('Job Completed', [
            'trace_id' => $this->traceId,
            'job_class' => static::class,
            'duration_ms' => round($duration * 1000, 2),
            'memory_after' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'result' => $result,
        ]);
    }
    
    protected function logJobFailure(\Exception $e)
    {
        $duration = microtime(true) - $this->startTime;
        
        Log::channel('queue-debug')->error('Job Failed', [
            'trace_id' => $this->traceId,
            'job_class' => static::class,
            'duration_ms' => round($duration * 1000, 2),
            'error_class' => get_class($e),
            'error_message' => $e->getMessage(),
            'error_code' => $e->getCode(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'stack_trace' => $e->getTraceAsString(),
        ]);
    }
    
    protected function logCheckpoint($message, $data = [])
    {
        Log::channel('queue-debug')->debug('Job Checkpoint', [
            'trace_id' => $this->traceId,
            'job_class' => static::class,
            'checkpoint' => $message,
            'data' => $data,
            'memory_current' => memory_get_usage(true),
            'timestamp' => now()->toISOString(),
        ]);
    }
}
```

### Performance-Profiling

#### Job-Performance-Profiler

```php
// app/Services/JobProfiler.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class JobProfiler
{
    private static $profiles = [];
    
    public static function start($jobId, $jobClass)
    {
        self::$profiles[$jobId] = [
            'job_class' => $jobClass,
            'start_time' => microtime(true),
            'start_memory' => memory_get_usage(true),
            'checkpoints' => [],
        ];
    }
    
    public static function checkpoint($jobId, $name, $data = [])
    {
        if (!isset(self::$profiles[$jobId])) {
            return;
        }
        
        $currentTime = microtime(true);
        $currentMemory = memory_get_usage(true);
        
        self::$profiles[$jobId]['checkpoints'][] = [
            'name' => $name,
            'timestamp' => $currentTime,
            'memory' => $currentMemory,
            'duration_from_start' => $currentTime - self::$profiles[$jobId]['start_time'],
            'memory_diff' => $currentMemory - self::$profiles[$jobId]['start_memory'],
            'data' => $data,
        ];
    }
    
    public static function end($jobId, $success = true, $error = null)
    {
        if (!isset(self::$profiles[$jobId])) {
            return;
        }
        
        $profile = self::$profiles[$jobId];
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $result = [
            'job_id' => $jobId,
            'job_class' => $profile['job_class'],
            'total_duration' => $endTime - $profile['start_time'],
            'total_memory_used' => $endMemory - $profile['start_memory'],
            'peak_memory' => memory_get_peak_usage(true),
            'success' => $success,
            'error' => $error,
            'checkpoints' => $profile['checkpoints'],
            'created_at' => now(),
        ];
        
        // In Datenbank speichern
        DB::table('job_profiles')->insert($result);
        
        // Aus Memory entfernen
        unset(self::$profiles[$jobId]);
        
        return $result;
    }
    
    public static function analyze($jobClass = null, $timeframe = '24h')
    {
        $query = DB::table('job_profiles')
            ->where('created_at', '>=', now()->sub($timeframe));
            
        if ($jobClass) {
            $query->where('job_class', $jobClass);
        }
        
        $profiles = $query->get();
        
        return [
            'total_jobs' => $profiles->count(),
            'successful_jobs' => $profiles->where('success', true)->count(),
            'failed_jobs' => $profiles->where('success', false)->count(),
            'avg_duration' => $profiles->avg('total_duration'),
            'max_duration' => $profiles->max('total_duration'),
            'min_duration' => $profiles->min('total_duration'),
            'avg_memory' => $profiles->avg('total_memory_used'),
            'max_memory' => $profiles->max('total_memory_used'),
            'slowest_jobs' => $profiles->sortByDesc('total_duration')->take(10),
            'memory_hungry_jobs' => $profiles->sortByDesc('total_memory_used')->take(10),
        ];
    }
}
```

#### Profiler in Jobs verwenden

```php
// app/Jobs/ProfiledEmailJob.php
<?php

namespace App\Jobs;

use App\Services\JobProfiler;

class ProfiledEmailJob extends TraceableJob
{
    private $user;
    private $emailType;
    
    public function __construct($user, $emailType)
    {
        parent::__construct();
        $this->user = $user;
        $this->emailType = $emailType;
    }
    
    protected function execute()
    {
        $jobId = $this->job->getJobId();
        
        JobProfiler::start($jobId, static::class);
        
        try {
            JobProfiler::checkpoint($jobId, 'user_loaded', ['user_id' => $this->user->id]);
            
            // E-Mail-Template laden
            $template = $this->loadEmailTemplate();
            JobProfiler::checkpoint($jobId, 'template_loaded', ['template' => $this->emailType]);
            
            // E-Mail-Daten vorbereiten
            $data = $this->prepareEmailData();
            JobProfiler::checkpoint($jobId, 'data_prepared', ['data_size' => strlen(serialize($data))]);
            
            // E-Mail senden
            $result = $this->sendEmail($template, $data);
            JobProfiler::checkpoint($jobId, 'email_sent', ['message_id' => $result['message_id']]);
            
            JobProfiler::end($jobId, true);
            
            return $result;
            
        } catch (\Exception $e) {
            JobProfiler::end($jobId, false, $e->getMessage());
            throw $e;
        }
    }
    
    private function loadEmailTemplate()
    {
        // Template-Loading-Logik
        return "email.{$this->emailType}";
    }
    
    private function prepareEmailData()
    {
        // Daten-Vorbereitung
        return [
            'user' => $this->user->toArray(),
            'timestamp' => now(),
        ];
    }
    
    private function sendEmail($template, $data)
    {
        // E-Mail-Versand
        return ['message_id' => uniqid('msg_')];
    }
}
```

## 🧠 Memory-Debugging

### Memory-Leak-Detection

```php
// app/Services/MemoryDebugger.php
<?php

namespace App\Services;

class MemoryDebugger
{
    private static $snapshots = [];
    private static $tracking = false;
    
    public static function startTracking()
    {
        self::$tracking = true;
        self::$snapshots = [];
        self::takeSnapshot('start');
    }
    
    public static function takeSnapshot($label)
    {
        if (!self::$tracking) {
            return;
        }
        
        $snapshot = [
            'label' => $label,
            'timestamp' => microtime(true),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'objects' => self::countObjects(),
        ];
        
        self::$snapshots[] = $snapshot;
        
        return $snapshot;
    }
    
    public static function stopTracking()
    {
        if (!self::$tracking) {
            return [];
        }
        
        self::takeSnapshot('end');
        self::$tracking = false;
        
        return self::analyzeSnapshots();
    }
    
    private static function countObjects()
    {
        $objects = [];
        
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
        
        foreach (get_declared_classes() as $class) {
            $reflection = new \ReflectionClass($class);
            if (!$reflection->isInternal()) {
                $objects[$class] = 0;
            }
        }
        
        return $objects;
    }
    
    private static function analyzeSnapshots()
    {
        if (count(self::$snapshots) < 2) {
            return [];
        }
        
        $analysis = [];
        $first = self::$snapshots[0];
        $last = end(self::$snapshots);
        
        $analysis['total_memory_increase'] = $last['memory_usage'] - $first['memory_usage'];
        $analysis['peak_memory_increase'] = $last['memory_peak'] - $first['memory_peak'];
        $analysis['duration'] = $last['timestamp'] - $first['timestamp'];
        
        // Memory-Leaks identifizieren
        $analysis['potential_leaks'] = [];
        
        if ($analysis['total_memory_increase'] > 10 * 1024 * 1024) { // 10MB
            $analysis['potential_leaks'][] = [
                'type' => 'high_memory_increase',
                'increase' => $analysis['total_memory_increase'],
                'severity' => 'high',
            ];
        }
        
        // Snapshots für detaillierte Analyse
        $analysis['snapshots'] = self::$snapshots;
        
        return $analysis;
    }
    
    public static function dumpMemoryUsage()
    {
        $usage = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = self::parseMemoryLimit(ini_get('memory_limit'));
        
        echo "=== MEMORY USAGE ===\n";
        echo "Current: " . self::formatBytes($usage) . "\n";
        echo "Peak: " . self::formatBytes($peak) . "\n";
        echo "Limit: " . self::formatBytes($limit) . "\n";
        echo "Usage: " . round(($usage / $limit) * 100, 2) . "%\n";
        echo "===================\n";
    }
    
    private static function formatBytes($bytes)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    private static function parseMemoryLimit($limit)
    {
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

### Memory-optimierte Jobs

```php
// app/Jobs/MemoryOptimizedJob.php
<?php

namespace App\Jobs;

use App\Services\MemoryDebugger;

class MemoryOptimizedJob extends TraceableJob
{
    protected function execute()
    {
        MemoryDebugger::startTracking();
        
        try {
            // Chunked Processing für große Datenmengen
            $this->processInChunks();
            
            $analysis = MemoryDebugger::stopTracking();
            
            if ($analysis['total_memory_increase'] > 50 * 1024 * 1024) { // 50MB
                $this->logCheckpoint('high_memory_usage', $analysis);
            }
            
        } catch (\Exception $e) {
            MemoryDebugger::stopTracking();
            throw $e;
        }
    }
    
    private function processInChunks()
    {
        $chunkSize = 1000;
        $offset = 0;
        
        do {
            MemoryDebugger::takeSnapshot("chunk_start_{$offset}");
            
            // Daten in Chunks verarbeiten
            $data = $this->loadDataChunk($offset, $chunkSize);
            
            if (!empty($data)) {
                $this->processChunk($data);
                
                // Memory explizit freigeben
                unset($data);
                
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            }
            
            MemoryDebugger::takeSnapshot("chunk_end_{$offset}");
            
            $offset += $chunkSize;
            
        } while (!empty($data));
    }
    
    private function loadDataChunk($offset, $limit)
    {
        // Daten-Loading mit Limit
        return collect(range($offset, $offset + $limit - 1))
            ->take($limit)
            ->toArray();
    }
    
    private function processChunk($data)
    {
        foreach ($data as $item) {
            // Item-Verarbeitung
            $this->processItem($item);
        }
    }
    
    private function processItem($item)
    {
        // Einzelne Item-Verarbeitung
        return $item * 2;
    }
}
```

## 🌐 Network-Debugging

### HTTP-Request-Debugging

```php
// app/Services/NetworkDebugger.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NetworkDebugger
{
    public static function debugHttpRequest($url, $options = [])
    {
        $startTime = microtime(true);
        
        Log::info('HTTP Request Started', [
            'url' => $url,
            'options' => $options,
            'timestamp' => now()->toISOString(),
        ]);
        
        try {
            $response = Http::withOptions([
                'debug' => true,
                'timeout' => $options['timeout'] ?? 30,
                'connect_timeout' => $options['connect_timeout'] ?? 10,
            ])->get($url);
            
            $duration = microtime(true) - $startTime;
            
            Log::info('HTTP Request Completed', [
                'url' => $url,
                'status_code' => $response->status(),
                'duration_ms' => round($duration * 1000, 2),
                'response_size' => strlen($response->body()),
                'headers' => $response->headers(),
            ]);
            
            return $response;
            
        } catch (\Exception $e) {
            $duration = microtime(true) - $startTime;
            
            Log::error('HTTP Request Failed', [
                'url' => $url,
                'duration_ms' => round($duration * 1000, 2),
                'error' => $e->getMessage(),
                'error_class' => get_class($e),
            ]);
            
            throw $e;
        }