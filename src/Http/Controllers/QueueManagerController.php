<?php

namespace HenningD\LaravelQueueManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use HenningD\LaravelQueueManager\Models\QueueWorker;
use HenningD\LaravelQueueManager\Models\QueueConfiguration;

class QueueManagerController extends Controller
{
    /**
     * Queue Dashboard anzeigen
     */
    public function dashboard()
    {
        return view('queue-manager::dashboard');
    }

    /**
     * Queue-Status als JSON abrufen
     */
    public function status()
    {
        try {
            $data = [
                'config' => $this->getQueueConfig(),
                'statistics' => $this->getJobStatistics(),
                'pending_jobs' => $this->getPendingJobs(),
                'failed_jobs' => $this->getFailedJobs(),
                'recent_activity' => $this->getRecentActivity(),
                'workers_by_queue' => $this->getWorkersByQueue(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ];

            return response()->json($data);

        } catch (\Exception $e) {
            Log::error('Queue Status Error: ' . $e->getMessage());
            
            return response()->json([
                'error' => 'Fehler beim Abrufen des Queue-Status',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Worker-Konfigurationen abrufen
     */
    public function getWorkers()
    {
        try {
            $workers = QueueWorker::orderBy('name')->get();
            
            // Status für jeden Worker aktualisieren
            $workers->each(function ($worker) {
                $worker->updateStatus();
            });
            
            return response()->json([
                'success' => true,
                'workers' => $workers->map(function ($worker) {
                    return [
                        'id' => $worker->id,
                        'name' => $worker->name,
                        'queue' => $worker->queue,
                        'processes' => $worker->processes,
                        'timeout' => $worker->timeout,
                        'sleep' => $worker->sleep,
                        'max_tries' => $worker->max_tries,
                        'memory' => $worker->memory,
                        'is_active' => $worker->is_active,
                        'auto_restart' => $worker->auto_restart,
                        'environment_variables' => $worker->environment_variables,
                        'description' => $worker->description,
                        'status' => $worker->status,
                        'pid' => $worker->pid,
                        'last_started_at' => $worker->last_started_at?->format('Y-m-d H:i:s'),
                        'last_stopped_at' => $worker->last_stopped_at?->format('Y-m-d H:i:s'),
                        'created_at' => $worker->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $worker->updated_at->format('Y-m-d H:i:s'),
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Abrufen der Worker: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Neuen Worker erstellen
     */
    public function createWorker(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:queue_workers,name',
                'queue' => 'required|string|max:255',
                'processes' => 'integer|min:1|max:10',
                'timeout' => 'integer|min:30|max:3600',
                'sleep' => 'integer|min:1|max:60',
                'max_tries' => 'integer|min:1|max:10',
                'memory' => 'integer|min:64|max:1024',
                'is_active' => 'boolean',
                'auto_restart' => 'boolean',
                'environment_variables' => 'nullable|array',
                'description' => 'nullable|string|max:500',
            ]);

            $worker = QueueWorker::create($validated);

            return response()->json([
                'success' => true,
                'message' => "Worker '{$worker->name}' erfolgreich erstellt",
                'worker' => $worker
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Erstellen des Workers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Worker starten
     */
    public function startWorker($id)
    {
        try {
            $worker = QueueWorker::findOrFail($id);
            
            if (!$worker->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => "Worker '{$worker->name}' ist nicht aktiv"
                ]);
            }
            
            if ($worker->start()) {
                return response()->json([
                    'success' => true,
                    'message' => "Worker '{$worker->name}' erfolgreich gestartet"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Fehler beim Starten von Worker '{$worker->name}'"
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Starten des Workers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Worker stoppen
     */
    public function stopWorker($id)
    {
        try {
            $worker = QueueWorker::findOrFail($id);
            
            if ($worker->stop()) {
                return response()->json([
                    'success' => true,
                    'message' => "Worker '{$worker->name}' erfolgreich gestoppt"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Fehler beim Stoppen von Worker '{$worker->name}'"
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Stoppen des Workers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Queue-Konfigurationen abrufen
     */
    public function getQueues()
    {
        try {
            $queues = QueueConfiguration::with('workers')->orderBy('priority', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'queues' => $queues->map(function ($queue) {
                    return [
                        'id' => $queue->id,
                        'name' => $queue->name,
                        'display_name' => $queue->display_name,
                        'description' => $queue->description,
                        'priority' => $queue->priority,
                        'is_active' => $queue->is_active,
                        'max_jobs_per_minute' => $queue->max_jobs_per_minute,
                        'retry_delay' => $queue->retry_delay,
                        'allowed_job_types' => $queue->allowed_job_types,
                        'configuration' => $queue->configuration,
                        'pending_jobs_count' => $queue->pending_jobs_count,
                        'failed_jobs_count' => $queue->failed_jobs_count,
                        'active_workers_count' => $queue->active_workers_count,
                        'running_workers_count' => $queue->running_workers_count,
                        'priority_badge' => $queue->priority_badge,
                        'status_badge' => $queue->status_badge,
                        'created_at' => $queue->created_at->format('Y-m-d H:i:s'),
                        'updated_at' => $queue->updated_at->format('Y-m-d H:i:s'),
                    ];
                })
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Abrufen der Queues: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Neue Queue erstellen
     */
    public function createQueue(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:queue_configurations,name',
                'display_name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'priority' => 'integer|min:0|max:100',
                'is_active' => 'boolean',
                'max_jobs_per_minute' => 'integer|min:0|max:1000',
                'retry_delay' => 'integer|min:0|max:3600',
                'allowed_job_types' => 'nullable|array',
                'configuration' => 'nullable|array',
            ]);

            $queue = QueueConfiguration::create($validated);

            return response()->json([
                'success' => true,
                'message' => "Queue '{$queue->display_name}' erfolgreich erstellt",
                'queue' => $queue
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fehler beim Erstellen der Queue: ' . $e->getMessage()
            ], 500);
        }
    }

    // Weitere Methoden für Queue-Konfiguration, Job-Statistiken etc.
    // (gekürzt für Übersichtlichkeit - vollständige Implementierung verfügbar)

    private function getQueueConfig()
    {
        return [
            'default_connection' => config('queue.default'),
            'driver' => config('queue.connections.' . config('queue.default') . '.driver'),
            'table' => config('queue.connections.database.table', 'jobs'),
            'failed_table' => config('queue.failed.table', 'failed_jobs')
        ];
    }

    private function getJobStatistics()
    {
        $pendingJobs = DB::table('jobs')->count();
        $failedJobs = DB::table('failed_jobs')->count();
        
        return [
            'pending_total' => $pendingJobs,
            'failed_total' => $failedJobs,
            'worker_status' => 'running',
            'running_workers' => 0,
        ];
    }

    private function getPendingJobs()
    {
        return DB::table('jobs')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                
                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'job_name' => $payload['displayName'] ?? $payload['job'] ?? 'Unknown',
                    'attempts' => $job->attempts,
                    'created_at' => date('Y-m-d H:i:s', $job->created_at),
                ];
            });
    }

    private function getFailedJobs()
    {
        return DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($job) {
                $payload = json_decode($job->payload, true);
                
                return [
                    'id' => $job->id,
                    'queue' => $job->queue,
                    'job_name' => $payload['displayName'] ?? $payload['job'] ?? 'Unknown',
                    'failed_at' => $job->failed_at,
                    'exception' => substr($job->exception, 0, 200),
                ];
            });
    }

    private function getRecentActivity()
    {
        return [];
    }

    private function getWorkersByQueue()
    {
        return [];
    }
}