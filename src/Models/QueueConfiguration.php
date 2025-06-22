<?php

namespace HenningD\LaravelQueueManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QueueConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'priority',
        'is_active',
        'max_jobs_per_minute',
        'retry_delay',
        'allowed_job_types',
        'configuration',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allowed_job_types' => 'array',
        'configuration' => 'array',
    ];

    /**
     * Scope für aktive Queues
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope für Queues nach Priorität sortiert
     */
    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'desc');
    }

    /**
     * Prüft ob ein Job-Typ in dieser Queue erlaubt ist
     */
    public function allowsJobType(string $jobType): bool
    {
        if (empty($this->allowed_job_types)) {
            return true; // Wenn keine Einschränkungen, dann alle erlaubt
        }

        return in_array($jobType, $this->allowed_job_types);
    }

    /**
     * Gibt die Anzahl der aktuell wartenden Jobs zurück
     */
    public function getPendingJobsCountAttribute(): int
    {
        return DB::table('jobs')->where('queue', $this->name)->count();
    }

    /**
     * Gibt die Anzahl der fehlgeschlagenen Jobs zurück
     */
    public function getFailedJobsCountAttribute(): int
    {
        return DB::table('failed_jobs')->where('queue', $this->name)->count();
    }

    /**
     * Gibt die zugewiesenen Worker zurück
     */
    public function workers()
    {
        return $this->hasMany(QueueWorker::class, 'queue', 'name');
    }

    /**
     * Gibt die Anzahl der aktiven Worker zurück
     */
    public function getActiveWorkersCountAttribute(): int
    {
        return $this->workers()->where('is_active', true)->count();
    }

    /**
     * Gibt die Anzahl der laufenden Worker zurück
     */
    public function getRunningWorkersCountAttribute(): int
    {
        return $this->workers()->where('status', 'running')->count();
    }

    /**
     * Priority Badge für die UI
     */
    public function getPriorityBadgeAttribute(): array
    {
        if ($this->priority >= 80) {
            return ['color' => 'red', 'label' => 'Sehr hoch'];
        } elseif ($this->priority >= 60) {
            return ['color' => 'orange', 'label' => 'Hoch'];
        } elseif ($this->priority >= 40) {
            return ['color' => 'yellow', 'label' => 'Mittel'];
        } elseif ($this->priority >= 20) {
            return ['color' => 'blue', 'label' => 'Niedrig'];
        } else {
            return ['color' => 'gray', 'label' => 'Sehr niedrig'];
        }
    }

    /**
     * Status Badge für die UI
     */
    public function getStatusBadgeAttribute(): array
    {
        $runningWorkers = $this->running_workers_count;
        $pendingJobs = $this->pending_jobs_count;

        if (!$this->is_active) {
            return ['color' => 'gray', 'label' => 'Deaktiviert'];
        } elseif ($runningWorkers > 0) {
            return ['color' => 'green', 'label' => 'Aktiv'];
        } elseif ($pendingJobs > 0) {
            return ['color' => 'yellow', 'label' => 'Wartend'];
        } else {
            return ['color' => 'blue', 'label' => 'Bereit'];
        }
    }

    /**
     * Startet alle Worker für diese Queue
     */
    public function startAllWorkers(): array
    {
        $results = [];
        $workers = $this->workers()->where('is_active', true)->get();

        foreach ($workers as $worker) {
            $results[] = [
                'worker' => $worker->name,
                'success' => $worker->start(),
            ];
        }

        return $results;
    }

    /**
     * Stoppt alle Worker für diese Queue
     */
    public function stopAllWorkers(): array
    {
        $results = [];
        $workers = $this->workers()->where('status', 'running')->get();

        foreach ($workers as $worker) {
            $results[] = [
                'worker' => $worker->name,
                'success' => $worker->stop(),
            ];
        }

        return $results;
    }

    /**
     * Löscht alle Jobs aus dieser Queue
     */
    public function clearJobs(): int
    {
        return DB::table('jobs')->where('queue', $this->name)->delete();
    }

    /**
     * Löscht alle fehlgeschlagenen Jobs aus dieser Queue
     */
    public function clearFailedJobs(): int
    {
        return DB::table('failed_jobs')->where('queue', $this->name)->delete();
    }
}