# Anpassungen

Diese Anleitung zeigt dir, wie du das Laravel Queue Manager Package an deine spezifischen Bedürfnisse anpasst. Du lernst, wie du die Benutzeroberfläche, Funktionalität und das Verhalten des Systems individuell konfigurierst.

## 🎯 Übersicht

Anpassungsmöglichkeiten umfassen:

1. **UI-Anpassungen** - Design, Layout und Themes
2. **Funktionale Erweiterungen** - Custom Commands und Services
3. **Event-Hooks** - Custom Event-Listener und Handlers
4. **View-Anpassungen** - Templates und Blade-Views
5. **API-Erweiterungen** - Custom Endpoints und Middleware
6. **Dashboard-Widgets** - Eigene Dashboard-Komponenten
7. **Notification-Channels** - Custom Benachrichtigungskanäle
8. **Storage-Adapter** - Eigene Storage-Implementierungen

## 🎨 UI-Anpassungen

### Custom Themes erstellen

#### Theme-Struktur

```php
// config/queue-manager.php
'dashboard' => [
    'theme' => 'corporate',
    'custom_themes' => [
        'corporate' => [
            'name' => 'Corporate Theme',
            'css' => resource_path('css/queue-manager-corporate.css'),
            'js' => resource_path('js/queue-manager-corporate.js'),
            'config' => [
                'primary_color' => '#1E40AF',
                'secondary_color' => '#64748B',
                'success_color' => '#059669',
                'warning_color' => '#D97706',
                'error_color' => '#DC2626',
                'background_color' => '#F8FAFC',
                'sidebar_color' => '#1E293B',
                'text_color' => '#0F172A',
            ],
        ],
    ],
],
```

#### CSS-Anpassungen

```css
/* resources/css/queue-manager-corporate.css */

/* CSS-Variablen überschreiben */
:root {
    --qm-primary: #1E40AF;
    --qm-secondary: #64748B;
    --qm-success: #059669;
    --qm-warning: #D97706;
    --qm-error: #DC2626;
    --qm-background: #F8FAFC;
    --qm-sidebar: #1E293B;
    --qm-text: #0F172A;
    --qm-border: #E2E8F0;
    --qm-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

/* Corporate Branding */
.queue-manager-header {
    background: linear-gradient(135deg, var(--qm-primary), var(--qm-secondary));
    border-bottom: 3px solid var(--qm-primary);
}

.queue-manager-logo::before {
    content: '';
    background-image: url('/images/corporate-logo.svg');
    background-size: contain;
    background-repeat: no-repeat;
    width: 40px;
    height: 40px;
    display: inline-block;
    margin-right: 10px;
}

/* Custom Card-Styles */
.queue-manager-card {
    border-radius: 12px;
    box-shadow: var(--qm-shadow);
    border: 1px solid var(--qm-border);
    transition: all 0.3s ease;
}

.queue-manager-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px -5px rgba(0, 0, 0, 0.1);
}

/* Custom Button-Styles */
.btn-primary {
    background: linear-gradient(135deg, var(--qm-primary), #3B82F6);
    border: none;
    border-radius: 8px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Responsive Anpassungen */
@media (max-width: 768px) {
    .queue-manager-sidebar {
        background: var(--qm-sidebar);
    }
    
    .queue-manager-mobile-header {
        background: var(--qm-primary);
    }
}

/* Dark Mode Support */
@media (prefers-color-scheme: dark) {
    :root {
        --qm-background: #0F172A;
        --qm-text: #F1F5F9;
        --qm-border: #334155;
    }
}
```

#### JavaScript-Anpassungen

```javascript
// resources/js/queue-manager-corporate.js

// Corporate Theme Initialisierung
document.addEventListener('DOMContentLoaded', function() {
    initializeCorporateTheme();
    setupCustomAnimations();
    initializeCustomWidgets();
});

function initializeCorporateTheme() {
    // Theme-spezifische Initialisierung
    const theme = {
        name: 'corporate',
        animations: true,
        transitions: 'smooth',
        chartColors: ['#1E40AF', '#059669', '#D97706', '#DC2626'],
    };
    
    // Theme in localStorage speichern
    localStorage.setItem('queueManagerTheme', JSON.stringify(theme));
    
    // Custom Event dispatchen
    window.dispatchEvent(new CustomEvent('themeLoaded', { detail: theme }));
}

function setupCustomAnimations() {
    // Smooth Scroll für Navigation
    document.querySelectorAll('.queue-manager-nav a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
    
    // Loading-Animationen
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in');
            }
        });
    });
    
    document.querySelectorAll('.queue-manager-card').forEach(card => {
        observer.observe(card);
    });
}

function initializeCustomWidgets() {
    // Custom Dashboard-Widgets
    if (typeof Alpine !== 'undefined') {
        Alpine.data('corporateWidget', () => ({
            data: {},
            loading: false,
            
            init() {
                this.loadData();
                setInterval(() => this.loadData(), 30000); // 30 Sekunden
            },
            
            async loadData() {
                this.loading = true;
                try {
                    const response = await fetch('/queue-manager/api/corporate-metrics');
                    this.data = await response.json();
                } catch (error) {
                    console.error('Fehler beim Laden der Corporate-Metriken:', error);
                } finally {
                    this.loading = false;
                }
            }
        }));
    }
}

// Custom Chart-Konfiguration
window.queueManagerChartConfig = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                padding: 20,
                font: {
                    family: 'Inter, sans-serif',
                    size: 12,
                }
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            grid: {
                color: 'rgba(148, 163, 184, 0.1)',
            },
            ticks: {
                font: {
                    family: 'Inter, sans-serif',
                }
            }
        },
        x: {
            grid: {
                display: false,
            },
            ticks: {
                font: {
                    family: 'Inter, sans-serif',
                }
            }
        }
    }
};
```

### Logo und Branding anpassen

#### Custom Logo einbinden

```php
// config/queue-manager.php
'branding' => [
    'logo' => [
        'path' => '/images/company-logo.svg',
        'alt' => 'Company Name',
        'width' => 40,
        'height' => 40,
    ],
    'company_name' => 'Your Company',
    'tagline' => 'Queue Management System',
    'favicon' => '/images/favicon.ico',
],
```

#### Blade-Template für Header anpassen

```blade
{{-- resources/views/vendor/queue-manager/partials/header.blade.php --}}
<header class="queue-manager-header">
    <div class="container mx-auto px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            @if(config('queue-manager.branding.logo.path'))
                <img src="{{ asset(config('queue-manager.branding.logo.path')) }}" 
                     alt="{{ config('queue-manager.branding.logo.alt') }}"
                     width="{{ config('queue-manager.branding.logo.width') }}"
                     height="{{ config('queue-manager.branding.logo.height') }}"
                     class="queue-manager-logo">
            @endif
            
            <div>
                <h1 class="text-xl font-bold text-white">
                    {{ config('queue-manager.branding.company_name', 'Queue Manager') }}
                </h1>
                @if(config('queue-manager.branding.tagline'))
                    <p class="text-sm text-gray-200">
                        {{ config('queue-manager.branding.tagline') }}
                    </p>
                @endif
            </div>
        </div>
        
        <div class="flex items-center space-x-4">
            {{-- User-Menu, Settings, etc. --}}
            @include('queue-manager::partials.user-menu')
        </div>
    </div>
</header>
```

## 🔧 Funktionale Erweiterungen

### Custom Commands erstellen

#### Eigene Artisan-Commands

```php
// app/Console/Commands/CustomQueueCommand.php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use HenningD\LaravelQueueManager\Services\QueueManagerService;

class CustomQueueCommand extends Command
{
    protected $signature = 'queue:custom-operation {action} {--queue=} {--force}';
    protected $description = 'Führt custom Queue-Operationen aus';

    public function handle(QueueManagerService $queueManager)
    {
        $action = $this->argument('action');
        $queue = $this->option('queue');
        $force = $this->option('force');

        switch ($action) {
            case 'health-check':
                $this->performHealthCheck($queueManager, $queue);
                break;
                
            case 'optimize':
                $this->optimizeQueue($queueManager, $queue, $force);
                break;
                
            case 'backup':
                $this->backupQueueData($queueManager);
                break;
                
            default:
                $this->error("Unbekannte Aktion: {$action}");
                return 1;
        }

        return 0;
    }

    private function performHealthCheck($queueManager, $queue)
    {
        $this->info('Führe Queue Health-Check durch...');
        
        $queues = $queue ? [$queue] : $queueManager->getAllQueues();
        
        foreach ($queues as $queueName) {
            $health = $queueManager->checkQueueHealth($queueName);
            
            $status = $health['healthy'] ? '✅' : '❌';
            $this->line("{$status} Queue: {$queueName}");
            
            if (!$health['healthy']) {
                foreach ($health['issues'] as $issue) {
                    $this->warn("  - {$issue}");
                }
            }
        }
    }

    private function optimizeQueue($queueManager, $queue, $force)
    {
        $this->info('Optimiere Queue-Performance...');
        
        if (!$force && !$this->confirm('Queue-Optimierung kann Performance beeinträchtigen. Fortfahren?')) {
            return;
        }
        
        $result = $queueManager->optimizeQueue($queue);
        
        $this->info("Optimierung abgeschlossen:");
        $this->line("- Bereinigte Jobs: {$result['cleaned_jobs']}");
        $this->line("- Optimierte Indizes: {$result['optimized_indexes']}");
        $this->line("- Gesparte Speicher: {$result['saved_space']} MB");
    }

    private function backupQueueData($queueManager)
    {
        $this->info('Erstelle Queue-Backup...');
        
        $backupPath = $queueManager->createBackup();
        
        $this->info("Backup erstellt: {$backupPath}");
    }
}
```

#### Command in ServiceProvider registrieren

```php
// app/Providers/AppServiceProvider.php
public function boot()
{
    if ($this->app->runningInConsole()) {
        $this->commands([
            \App\Console\Commands\CustomQueueCommand::class,
        ]);
    }
}
```

### Custom Services erstellen

#### Queue Analytics Service

```php
// app/Services/QueueAnalyticsService.php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class QueueAnalyticsService
{
    public function getAdvancedMetrics($timeframe = '24h')
    {
        $cacheKey = "queue_analytics_{$timeframe}";
        
        return Cache::remember($cacheKey, 300, function () use ($timeframe) {
            $since = $this->parseTimeframe($timeframe);
            
            return [
                'throughput' => $this->calculateThroughput($since),
                'efficiency' => $this->calculateEfficiency($since),
                'bottlenecks' => $this->identifyBottlenecks($since),
                'predictions' => $this->generatePredictions($since),
                'recommendations' => $this->generateRecommendations($since),
            ];
        });
    }

    private function calculateThroughput($since)
    {
        return DB::table('jobs_history')
            ->where('completed_at', '>=', $since)
            ->selectRaw('
                queue,
                COUNT(*) as total_jobs,
                AVG(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as avg_duration,
                MIN(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as min_duration,
                MAX(TIMESTAMPDIFF(SECOND, started_at, completed_at)) as max_duration
            ')
            ->groupBy('queue')
            ->get();
    }

    private function calculateEfficiency($since)
    {
        $data = DB::table('jobs_history')
            ->where('completed_at', '>=', $since)
            ->selectRaw('
                queue,
                COUNT(*) as total_jobs,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as successful_jobs,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_jobs,
                AVG(memory_usage_mb) as avg_memory,
                AVG(cpu_usage_percent) as avg_cpu
            ')
            ->groupBy('queue')
            ->get();

        return $data->map(function ($item) {
            $item->success_rate = $item->total_jobs > 0 
                ? ($item->successful_jobs / $item->total_jobs) * 100 
                : 0;
            $item->failure_rate = $item->total_jobs > 0 
                ? ($item->failed_jobs / $item->total_jobs) * 100 
                : 0;
            return $item;
        });
    }

    private function identifyBottlenecks($since)
    {
        // Identifiziere Queues mit langen Wartezeiten
        $longWaitTimes = DB::table('jobs_history')
            ->where('completed_at', '>=', $since)
            ->selectRaw('
                queue,
                AVG(TIMESTAMPDIFF(SECOND, created_at, started_at)) as avg_wait_time
            ')
            ->groupBy('queue')
            ->having('avg_wait_time', '>', 60) // Mehr als 1 Minute
            ->orderBy('avg_wait_time', 'desc')
            ->get();

        // Identifiziere Queues mit hoher Fehlerrate
        $highErrorRates = DB::table('jobs_history')
            ->where('completed_at', '>=', $since)
            ->selectRaw('
                queue,
                COUNT(*) as total_jobs,
                SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) as failed_jobs,
                (SUM(CASE WHEN status = "failed" THEN 1 ELSE 0 END) / COUNT(*)) * 100 as error_rate
            ')
            ->groupBy('queue')
            ->having('error_rate', '>', 5) // Mehr als 5% Fehlerrate
            ->orderBy('error_rate', 'desc')
            ->get();

        return [
            'long_wait_times' => $longWaitTimes,
            'high_error_rates' => $highErrorRates,
        ];
    }

    private function generatePredictions($since)
    {
        // Einfache lineare Regression für Job-Volumen-Vorhersage
        $hourlyData = DB::table('jobs_history')
            ->where('completed_at', '>=', $since)
            ->selectRaw('
                HOUR(completed_at) as hour,
                COUNT(*) as job_count
            ')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        $predictions = [];
        foreach (range(0, 23) as $hour) {
            $historicalAvg = $hourlyData->where('hour', $hour)->avg('job_count') ?? 0;
            $predictions[$hour] = round($historicalAvg * 1.1); // 10% Wachstum angenommen
        }

        return $predictions;
    }

    private function generateRecommendations($since)
    {
        $recommendations = [];
        
        // Analysiere Worker-Auslastung
        $workerUtilization = DB::table('worker_metrics')
            ->where('recorded_at', '>=', $since)
            ->selectRaw('
                queue,
                AVG(cpu_usage) as avg_cpu,
                AVG(memory_usage) as avg_memory,
                COUNT(DISTINCT worker_id) as worker_count
            ')
            ->groupBy('queue')
            ->get();

        foreach ($workerUtilization as $queue) {
            if ($queue->avg_cpu > 80) {
                $recommendations[] = [
                    'type' => 'scale_up',
                    'queue' => $queue->queue,
                    'message' => "Queue {$queue->queue} hat hohe CPU-Auslastung ({$queue->avg_cpu}%). Erwäge mehr Worker.",
                    'priority' => 'high',
                ];
            }
            
            if ($queue->avg_cpu < 20 && $queue->worker_count > 1) {
                $recommendations[] = [
                    'type' => 'scale_down',
                    'queue' => $queue->queue,
                    'message' => "Queue {$queue->queue} hat niedrige CPU-Auslastung ({$queue->avg_cpu}%). Worker reduzieren möglich.",
                    'priority' => 'medium',
                ];
            }
        }

        return $recommendations;
    }

    private function parseTimeframe($timeframe)
    {
        switch ($timeframe) {
            case '1h': return Carbon::now()->subHour();
            case '24h': return Carbon::now()->subDay();
            case '7d': return Carbon::now()->subWeek();
            case '30d': return Carbon::now()->subMonth();
            default: return Carbon::now()->subDay();
        }
    }
}
```

## 🎣 Event-Hooks und Listeners

### Custom Event-Listeners

#### Queue-Event-Listener

```php
// app/Listeners/CustomQueueEventListener.php
<?php

namespace App\Listeners;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\WorkerStopping;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CustomQueueEventListener
{
    public function handleJobProcessed(JobProcessed $event)
    {
        // Custom Logik nach Job-Verarbeitung
        $jobName = $event->job->resolveName();
        $queue = $event->job->getQueue();
        
        // Performance-Metriken sammeln
        $this->recordPerformanceMetrics($jobName, $queue, $event);
        
        // Custom Notifications
        if ($this->isHighPriorityJob($jobName)) {
            $this->notifyJobCompletion($jobName, $queue);
        }
        
        // Cache-Updates
        $this->updateQueueStatistics($queue);
    }

    public function handleJobFailed(JobFailed $event)
    {
        $jobName = $event->job->resolveName();
        $queue = $event->job->getQueue();
        $exception = $event->exception;
        
        // Erweiterte Fehler-Analyse
        $this->analyzeFailure($jobName, $queue, $exception);
        
        // Auto-Recovery versuchen
        if ($this->shouldAttemptAutoRecovery($jobName, $exception)) {
            $this->attemptAutoRecovery($event->job);
        }
        
        // Incident-Management
        if ($this->isCriticalFailure($jobName, $exception)) {
            $this->createIncident($jobName, $queue, $exception);
        }
    }

    public function handleWorkerStopping(WorkerStopping $event)
    {
        // Graceful Shutdown-Logik
        $this->logWorkerShutdown($event);
        $this->cleanupWorkerResources($event);
        $this->notifyWorkerShutdown($event);
    }

    private function recordPerformanceMetrics($jobName, $queue, $event)
    {
        $metrics = [
            'job_name' => $jobName,
            'queue' => $queue,
            'duration' => microtime(true) - $event->job->getStartTime(),
            'memory_usage' => memory_get_peak_usage(true),
            'timestamp' => now(),
        ];
        
        // In Datenbank oder Cache speichern
        Cache::put("job_metrics_{$event->job->getJobId()}", $metrics, 3600);
    }

    private function isHighPriorityJob($jobName)
    {
        $highPriorityJobs = [
            'App\\Jobs\\SendCriticalEmail',
            'App\\Jobs\\ProcessPayment',
            'App\\Jobs\\SecurityAlert',
        ];
        
        return in_array($jobName, $highPriorityJobs);
    }

    private function analyzeFailure($jobName, $queue, $exception)
    {
        $analysis = [
            'job_name' => $jobName,
            'queue' => $queue,
            'error_type' => get_class($exception),
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode(),
            'stack_trace' => $exception->getTraceAsString(),
            'timestamp' => now(),
        ];
        
        // Fehler-Pattern erkennen
        $pattern = $this->identifyErrorPattern($analysis);
        if ($pattern) {
            $analysis['pattern'] = $pattern;
            $analysis['suggested_fix'] = $this->getSuggestedFix($pattern);
        }
        
        Log::error('Job Failure Analysis', $analysis);
    }

    private function shouldAttemptAutoRecovery($jobName, $exception)
    {
        // Nur bei bestimmten Fehlern Auto-Recovery versuchen
        $recoverableExceptions = [
            'Illuminate\\Database\\QueryException',
            'GuzzleHttp\\Exception\\ConnectException',
            'Predis\\Connection\\ConnectionException',
        ];
        
        return in_array(get_class($exception), $recoverableExceptions);
    }

    private function attemptAutoRecovery($job)
    {
        // Warte kurz und versuche Job erneut
        sleep(5);
        
        try {
            $job->fire();
            Log::info('Auto-Recovery erfolgreich', ['job_id' => $job->getJobId()]);
        } catch (\Exception $e) {
            Log::warning('Auto-Recovery fehlgeschlagen', [
                'job_id' => $job->getJobId(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

#### Event-Listener registrieren

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    'Illuminate\Queue\Events\JobProcessed' => [
        'App\Listeners\CustomQueueEventListener@handleJobProcessed',
    ],
    'Illuminate\Queue\Events\JobFailed' => [
        'App\Listeners\CustomQueueEventListener@handleJobFailed',
    ],
    'Illuminate\Queue\Events\WorkerStopping' => [
        'App\Listeners\CustomQueueEventListener@handleWorkerStopping',
    ],
];
```

## 📱 Dashboard-Widgets anpassen

### Custom Dashboard-Widget erstellen

#### Widget-Komponente

```php
// app/View/Components/QueueManagerWidget.php
<?php

namespace App\View\Components;

use Illuminate\View\Component;
use App\Services\QueueAnalyticsService;

class QueueManagerWidget extends Component
{
    public $title;
    public $type;
    public $data;
    public $refreshInterval;

    public function __construct($title, $type, $refreshInterval = 30000)
    {
        $this->title = $title;
        $this->type = $type;
        $this->refreshInterval = $refreshInterval;
    }

    public function render()
    {
        $this->data = $this->loadWidgetData();
        
        return view('components.queue-manager-widget');
    }

    private function loadWidgetData()
    {
        $analytics = app(QueueAnalyticsService::class);
        
        switch ($this->type) {
            case 'throughput':
                return $analytics->getThroughputData();
            case 'efficiency':
                return $analytics->getEfficiencyData();
            case 'predictions':
                return $analytics->getPredictionData();
            default:
                return [];
        }
    }
}
```

#### Widget-Template

```blade
{{-- resources/views/components/queue-manager-widget.blade.php --}}
<div class="queue-manager-widget bg-white rounded-lg shadow-md p-6" 
     x-data="queueWidget('{{ $type }}', {{ $refreshInterval }})"
     x-init="init()">
    
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-800">{{ $title }}</h3>
        <div class="flex items-center space-x-2">
            <button @click="refresh()" 
                    class="text-gray-500 hover:text-gray-700"
                    :disabled="loading">
                <svg class="w-4 h-4" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
            </button>
            <div class="w-2 h-2 rounded-full" 
                 :class="connected ? 'bg-green-500' : 'bg-red-500'"></div>
        </div>
    </div>
    
    <div class="widget-content" x-show="!loading">
        @switch($type)
            @case('throughput')
                @include('components.widgets.throughput', ['data' => $data])
                @break
            @case('efficiency')
                @include('components.widgets.efficiency', ['data' => $data])
                @break
            @case('predictions')
                @include('components.widgets.predictions', ['data' => $data])
                @break
            @default
                <p class="text-gray-500">Widget-Typ nicht unterstützt</p>
        @endswitch
    </div>
    
    <div x-show="loading" class="flex items-center justify-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>
</div>

<script>
function queueWidget(type, refreshInterval) {
    return {
        type: type,
        refreshInterval: refreshInterval,
        loading: false,
        connected: true,
        data: @json($data),
        
        init() {
            this.startAutoRefresh();
        },
        
        async refresh() {
            this.loading = true;
            try {
                const response = await fetch(`/queue-manager/api/widget/${this.type}`);
                if (response.ok) {
                    this.data = await response.json();
                    this.connected = true;
                } else {
                    this.connected = false;
                }
            } catch (error) {
                console.error('Widget refresh failed:', error);
                this.connected = false;
            } finally {
                this.loading = false;
            }
        },
        
        startAutoRefresh() {
            setInterval(() => {
                if (!document.hidden) {
                    this.refresh();
                }
            }, this.refreshInterval);
        }
    }
}
</script>
```

#### Widget in Dashboard einbinden

```blade
{{-- resources/views/vendor/queue-manager/dashboard.blade.php --}}
@extends('queue-manager::layout')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Standard-Statistiken -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        @include('queue-manager::partials.stats-cards')
    </div>
    
    <!-- Custom Widgets -->
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
        <x-queue-manager