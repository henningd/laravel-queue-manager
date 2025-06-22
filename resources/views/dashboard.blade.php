@extends('queue-manager::layout')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <h1 class="h3 mb-0">
            <i class="fas fa-tachometer-alt me-2"></i>Queue Manager Dashboard
        </h1>
        <p class="text-muted">Überwachung und Verwaltung von Laravel Queues und Workern</p>
    </div>
</div>

<!-- Status Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card queue-stats h-100">
            <div class="card-body text-center">
                <i class="fas fa-list-ul fa-3x mb-3"></i>
                <h3 class="card-title">{{ $stats['total_queues'] ?? 0 }}</h3>
                <p class="card-text">Konfigurierte Queues</p>
                <small>{{ $stats['active_queues'] ?? 0 }} aktiv</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card worker-stats h-100">
            <div class="card-body text-center">
                <i class="fas fa-users fa-3x mb-3"></i>
                <h3 class="card-title">{{ $stats['total_workers'] ?? 0 }}</h3>
                <p class="card-text">Worker Prozesse</p>
                <small>{{ $stats['running_workers'] ?? 0 }} laufend</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card system-stats h-100">
            <div class="card-body text-center">
                <i class="fas fa-server fa-3x mb-3"></i>
                <h3 class="card-title">{{ $stats['total_jobs'] ?? 0 }}</h3>
                <p class="card-text">Jobs in Warteschlange</p>
                <small>{{ $stats['failed_jobs'] ?? 0 }} fehlgeschlagen</small>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-bolt me-2"></i>Schnellaktionen
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-success w-100" onclick="startAllWorkers()">
                            <i class="fas fa-play me-1"></i>Alle Worker starten
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-warning w-100" onclick="restartAllWorkers()">
                            <i class="fas fa-redo me-1"></i>Alle Worker neustarten
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-danger w-100" onclick="stopAllWorkers()">
                            <i class="fas fa-stop me-1"></i>Alle Worker stoppen
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button class="btn btn-info w-100" onclick="retryFailedJobs()">
                            <i class="fas fa-retry me-1"></i>Failed Jobs wiederholen
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Workers Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-users me-2"></i>Worker Prozesse
                </h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addWorkerModal">
                    <i class="fas fa-plus me-1"></i>Worker hinzufügen
                </button>
            </div>
            <div class="card-body">
                @if(empty($workers))
                    <div class="text-center py-4">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Keine Worker konfiguriert</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWorkerModal">
                            Ersten Worker hinzufügen
                        </button>
                    </div>
                @else
                    <div class="row">
                        @foreach($workers as $worker)
                            <div class="col-md-6 col-lg-4 mb-3">
                                <div class="card worker-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">{{ $worker->name }}</h6>
                                            <span class="badge status-badge {{ $worker->is_running ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $worker->is_running ? 'Läuft' : 'Gestoppt' }}
                                            </span>
                                        </div>
                                        <p class="card-text small text-muted mb-2">
                                            Queue: <strong>{{ $worker->queue }}</strong><br>
                                            Timeout: {{ $worker->timeout }}s<br>
                                            Memory: {{ $worker->memory }}MB
                                        </p>
                                        @if($worker->is_running)
                                            <p class="card-text small">
                                                <i class="fas fa-clock me-1"></i>
                                                Gestartet: {{ $worker->started_at ? $worker->started_at->diffForHumans() : 'Unbekannt' }}
                                            </p>
                                        @endif
                                        <div class="btn-group w-100" role="group">
                                            @if($worker->is_running)
                                                <button class="btn btn-warning btn-sm" onclick="stopWorker({{ $worker->id }})">
                                                    <i class="fas fa-stop"></i>
                                                </button>
                                                <button class="btn btn-info btn-sm" onclick="restartWorker({{ $worker->id }})">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            @else
                                                <button class="btn btn-success btn-sm" onclick="startWorker({{ $worker->id }})">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                            @endif
                                            <button class="btn btn-danger btn-sm" onclick="deleteWorker({{ $worker->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Queues Section -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list-ul me-2"></i>Queue Konfigurationen
                </h5>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addQueueModal">
                    <i class="fas fa-plus me-1"></i>Queue hinzufügen
                </button>
            </div>
            <div class="card-body">
                @if(empty($queues))
                    <div class="text-center py-4">
                        <i class="fas fa-list-ul fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Keine Queues konfiguriert</p>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQueueModal">
                            Erste Queue hinzufügen
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Connection</th>
                                    <th>Priorität</th>
                                    <th>Worker</th>
                                    <th>Jobs</th>
                                    <th>Status</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($queues as $queue)
                                    <tr>
                                        <td>
                                            <strong>{{ $queue->name }}</strong>
                                            @if($queue->description)
                                                <br><small class="text-muted">{{ $queue->description }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $queue->connection }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $queue->priority }}</span>
                                        </td>
                                        <td>{{ $queue->worker_count ?? 0 }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $queue->pending_jobs ?? 0 }}</span>
                                            @if(($queue->failed_jobs ?? 0) > 0)
                                                <span class="badge bg-danger">{{ $queue->failed_jobs }} failed</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $queue->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $queue->is_active ? 'Aktiv' : 'Inaktiv' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-success" onclick="startQueueWorkers({{ $queue->id }})" title="Worker starten">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                <button class="btn btn-warning" onclick="stopQueueWorkers({{ $queue->id }})" title="Worker stoppen">
                                                    <i class="fas fa-stop"></i>
                                                </button>
                                                <button class="btn btn-info" onclick="clearQueueJobs({{ $queue->id }})" title="Jobs löschen">
                                                    <i class="fas fa-broom"></i>
                                                </button>
                                                <button class="btn btn-danger" onclick="clearQueueFailedJobs({{ $queue->id }})" title="Failed Jobs löschen">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- System Information -->
<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2"></i>System Information
                </h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-6">Laravel Version:</dt>
                    <dd class="col-sm-6">{{ app()->version() }}</dd>
                    
                    <dt class="col-sm-6">PHP Version:</dt>
                    <dd class="col-sm-6">{{ PHP_VERSION }}</dd>
                    
                    <dt class="col-sm-6">Queue Driver:</dt>
                    <dd class="col-sm-6">{{ config('queue.default') }}</dd>
                    
                    <dt class="col-sm-6">Memory Limit:</dt>
                    <dd class="col-sm-6">{{ ini_get('memory_limit') }}</dd>
                    
                    <dt class="col-sm-6">Max Execution Time:</dt>
                    <dd class="col-sm-6">{{ ini_get('max_execution_time') }}s</dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>Performance Metriken
                </h5>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-6">Durchschnittliche Job-Zeit:</dt>
                    <dd class="col-sm-6">{{ $stats['avg_job_time'] ?? 'N/A' }}</dd>
                    
                    <dt class="col-sm-6">Jobs heute:</dt>
                    <dd class="col-sm-6">{{ $stats['jobs_today'] ?? 0 }}</dd>
                    
                    <dt class="col-sm-6">Erfolgsrate:</dt>
                    <dd class="col-sm-6">{{ $stats['success_rate'] ?? 'N/A' }}%</dd>
                    
                    <dt class="col-sm-6">Letzte Aktualisierung:</dt>
                    <dd class="col-sm-6">{{ now()->format('d.m.Y H:i:s') }}</dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Add Worker Modal -->
<div class="modal fade" id="addWorkerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Worker hinzufügen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addWorkerForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="workerName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="workerName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="workerQueue" class="form-label">Queue</label>
                        <input type="text" class="form-control" id="workerQueue" name="queue" value="default" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="workerTimeout" class="form-label">Timeout (Sekunden)</label>
                                <input type="number" class="form-control" id="workerTimeout" name="timeout" value="60" min="1">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="workerMemory" class="form-label">Memory Limit (MB)</label>
                                <input type="number" class="form-control" id="workerMemory" name="memory" value="128" min="64">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="workerAutoStart" name="auto_start" checked>
                            <label class="form-check-label" for="workerAutoStart">
                                Automatisch starten
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Worker erstellen</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Queue Modal -->
<div class="modal fade" id="addQueueModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Queue hinzufügen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addQueueForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="queueName" class="form-label">Name</label>
                        <input type="text" class="form-control" id="queueName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="queueDescription" class="form-label">Beschreibung</label>
                        <textarea class="form-control" id="queueDescription" name="description" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="queueConnection" class="form-label">Connection</label>
                                <select class="form-control" id="queueConnection" name="connection" required>
                                    <option value="database">Database</option>
                                    <option value="redis">Redis</option>
                                    <option value="sync">Sync</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="queuePriority" class="form-label">Priorität</label>
                                <input type="number" class="form-control" id="queuePriority" name="priority" value="1" min="1" max="10">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="queueActive" name="is_active" checked>
                            <label class="form-check-label" for="queueActive">
                                Aktiv
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Abbrechen</button>
                    <button type="submit" class="btn btn-primary">Queue erstellen</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Form Submissions
$('#addWorkerForm').on('submit', function(e) {
    e.preventDefault();
    
    const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
    $.post(`/${baseUrl}/workers`, $(this).serialize())
        .done(function(response) {
            $('#addWorkerModal').modal('hide');
            showAlert('Worker erfolgreich erstellt', 'success');
            setTimeout(refreshData, 1000);
        })
        .fail(function(xhr) {
            showAlert('Fehler beim Erstellen des Workers: ' + xhr.responseJSON?.message, 'danger');
        });
});

$('#addQueueForm').on('submit', function(e) {
    e.preventDefault();
    
    const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
    $.post(`/${baseUrl}/queues`, $(this).serialize())
        .done(function(response) {
            $('#addQueueModal').modal('hide');
            showAlert('Queue erfolgreich erstellt', 'success');
            setTimeout(refreshData, 1000);
        })
        .fail(function(xhr) {
            showAlert('Fehler beim Erstellen der Queue: ' + xhr.responseJSON?.message, 'danger');
        });
});

// Quick Actions
function startAllWorkers() {
    if (confirm('Alle Worker starten?')) {
        const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
        $.post(`/${baseUrl}/restart-workers`)
            .done(function(response) {
                showAlert('Alle Worker werden gestartet', 'success');
                setTimeout(refreshData, 2000);
            })
            .fail(function(xhr) {
                showAlert('Fehler beim Starten der Worker: ' + xhr.responseJSON?.message, 'danger');
            });
    }
}

function restartAllWorkers() {
    if (confirm('Alle Worker neustarten?')) {
        const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
        $.post(`/${baseUrl}/restart-workers`)
            .done(function(response) {
                showAlert('Alle Worker werden neugestartet', 'success');
                setTimeout(refreshData, 3000);
            })
            .fail(function(xhr) {
                showAlert('Fehler beim Neustarten der Worker: ' + xhr.responseJSON?.message, 'danger');
            });
    }
}

function stopAllWorkers() {
    if (confirm('Alle Worker stoppen?')) {
        // Implementation would need a stop-all endpoint
        showAlert('Funktion wird implementiert', 'info');
    }
}

function retryFailedJobs() {
    if (confirm('Alle fehlgeschlagenen Jobs wiederholen?')) {
        const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
        $.post(`/${baseUrl}/retry-failed`)
            .done(function(response) {
                showAlert('Fehlgeschlagene Jobs werden wiederholt', 'success');
                setTimeout(refreshData, 2000);
            })
            .fail(function(xhr) {
                showAlert('Fehler beim Wiederholen der Jobs: ' + xhr.responseJSON?.message, 'danger');
            });
    }
}

// Dashboard-spezifische Funktionen (Worker/Queue-Management ist bereits im Layout definiert)
</script>
@endpush