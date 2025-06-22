<?php

namespace HenningD\LaravelQueueManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QueueWorker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'queue',
        'processes',
        'timeout',
        'sleep',
        'max_tries',
        'memory',
        'is_active',
        'auto_restart',
        'environment_variables',
        'description',
        'last_started_at',
        'last_stopped_at',
        'status',
        'pid',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'auto_restart' => 'boolean',
        'environment_variables' => 'array',
        'last_started_at' => 'datetime',
        'last_stopped_at' => 'datetime',
    ];

    /**
     * Scope für aktive Worker
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope für laufende Worker
     */
    public function scopeRunning($query)
    {
        return $query->where('status', 'running');
    }

    /**
     * Prüft ob der Worker läuft
     */
    public function isRunning(): bool
    {
        if ($this->status !== 'running' || !$this->pid) {
            return false;
        }

        // Prüfe ob der Prozess noch existiert (Windows-kompatibel)
        if (PHP_OS_FAMILY === 'Windows') {
            $output = shell_exec("tasklist /FI \"PID eq {$this->pid}\" 2>NUL");
            return $output && strpos($output, (string)$this->pid) !== false;
        } else {
            return posix_kill($this->pid, 0);
        }
    }

    /**
     * Generiert den Queue-Work-Command
     */
    public function getCommandAttribute(): string
    {
        $command = 'php artisan queue:work';
        
        if ($this->queue !== 'default') {
            $command .= " --queue={$this->queue}";
        }
        
        $command .= " --timeout={$this->timeout}";
        $command .= " --sleep={$this->sleep}";
        $command .= " --tries={$this->max_tries}";
        $command .= " --memory={$this->memory}";
        
        return $command;
    }

    /**
     * Status-Badge für die UI
     */
    public function getStatusBadgeAttribute(): array
    {
        return match($this->status) {
            'running' => ['color' => 'success', 'label' => 'Läuft'],
            'stopped' => ['color' => 'gray', 'label' => 'Gestoppt'],
            'failed' => ['color' => 'danger', 'label' => 'Fehler'],
            default => ['color' => 'warning', 'label' => 'Unbekannt'],
        };
    }

    /**
     * Startet den Worker
     */
    public function start(): bool
    {
        if ($this->isRunning()) {
            return true;
        }

        $command = $this->command;
        
        // Umgebungsvariablen hinzufügen
        if ($this->environment_variables) {
            $envVars = [];
            foreach ($this->environment_variables as $key => $value) {
                $envVars[] = "{$key}={$value}";
            }
            $command = implode(' ', $envVars) . ' ' . $command;
        }

        // Worker im Hintergrund starten
        if (PHP_OS_FAMILY === 'Windows') {
            $command = "start /B {$command}";
            $pid = null; // Windows PID-Tracking ist komplexer
        } else {
            $command .= ' > /dev/null 2>&1 & echo $!';
            $pid = (int) shell_exec($command);
        }

        $this->update([
            'status' => 'running',
            'pid' => $pid,
            'last_started_at' => now(),
        ]);

        return true;
    }

    /**
     * Stoppt den Worker
     */
    public function stop(): bool
    {
        if (!$this->isRunning()) {
            $this->update(['status' => 'stopped', 'pid' => null]);
            return true;
        }

        if ($this->pid) {
            if (PHP_OS_FAMILY === 'Windows') {
                shell_exec("taskkill /PID {$this->pid} /F 2>NUL");
            } else {
                posix_kill($this->pid, SIGTERM);
            }
        }

        $this->update([
            'status' => 'stopped',
            'pid' => null,
            'last_stopped_at' => now(),
        ]);

        return true;
    }

    /**
     * Neustart des Workers
     */
    public function restart(): bool
    {
        $this->stop();
        sleep(2); // Kurz warten
        return $this->start();
    }

    /**
     * Aktualisiert den Worker-Status
     */
    public function updateStatus(): void
    {
        if ($this->status === 'running' && !$this->isRunning()) {
            $this->update([
                'status' => 'stopped',
                'pid' => null,
                'last_stopped_at' => now(),
            ]);
        }
    }
}