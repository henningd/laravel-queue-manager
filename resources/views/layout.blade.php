<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Laravel') }} - Queue Manager</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .worker-card {
            transition: all 0.3s ease;
        }
        .worker-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .queue-stats {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .worker-stats {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        .system-stats {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
        }
        .refresh-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
        .log-container {
            max-height: 400px;
            overflow-y: auto;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
        }
        .log-entry {
            padding: 0.5rem;
            border-bottom: 1px solid #e9ecef;
            font-family: 'Courier New', monospace;
            font-size: 0.875rem;
        }
        .log-entry:last-child {
            border-bottom: none;
        }
        .log-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .log-warning {
            background-color: #fff3cd;
            color: #856404;
        }
        .log-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('queue-manager.dashboard') }}">
                <i class="fas fa-cogs me-2"></i>Queue Manager
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item">
                    <span class="navbar-text me-3">
                        <i class="fas fa-clock me-1"></i>
                        <span id="current-time">{{ now()->format('H:i:s') }}</span>
                    </span>
                </div>
                <div class="nav-item">
                    <button class="btn btn-outline-light btn-sm" onclick="refreshData()">
                        <i class="fas fa-sync-alt me-1"></i>Aktualisieren
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        @yield('content')
    </div>

    <!-- Refresh Button -->
    <button class="btn btn-primary btn-lg refresh-btn" onclick="refreshData()" title="Daten aktualisieren">
        <i class="fas fa-sync-alt"></i>
    </button>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // CSRF Token Setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Auto-refresh every 30 seconds
        let autoRefreshInterval;
        
        function startAutoRefresh() {
            autoRefreshInterval = setInterval(refreshData, 30000);
        }
        
        function stopAutoRefresh() {
            if (autoRefreshInterval) {
                clearInterval(autoRefreshInterval);
            }
        }
        
        function refreshData() {
            location.reload();
        }
        
        // Update current time every second
        function updateTime() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('de-DE');
            document.getElementById('current-time').textContent = timeString;
        }
        
        setInterval(updateTime, 1000);
        
        // Start auto-refresh when page loads
        $(document).ready(function() {
            startAutoRefresh();
        });
        
        // Stop auto-refresh when page is hidden
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                stopAutoRefresh();
            } else {
                startAutoRefresh();
            }
        });
        
        // Worker Management Functions
        function startWorker(workerId) {
            const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
            $.post(`/${baseUrl}/workers/${workerId}/start`)
                .done(function(response) {
                    showAlert('Worker erfolgreich gestartet', 'success');
                    setTimeout(refreshData, 1000);
                })
                .fail(function(xhr) {
                    showAlert('Fehler beim Starten des Workers: ' + xhr.responseJSON?.message, 'danger');
                });
        }
        
        function stopWorker(workerId) {
            const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
            $.post(`/${baseUrl}/workers/${workerId}/stop`)
                .done(function(response) {
                    showAlert('Worker erfolgreich gestoppt', 'success');
                    setTimeout(refreshData, 1000);
                })
                .fail(function(xhr) {
                    showAlert('Fehler beim Stoppen des Workers: ' + xhr.responseJSON?.message, 'danger');
                });
        }
        
        function restartWorker(workerId) {
            const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
            $.post(`/${baseUrl}/workers/${workerId}/restart`)
                .done(function(response) {
                    showAlert('Worker erfolgreich neugestartet', 'success');
                    setTimeout(refreshData, 2000);
                })
                .fail(function(xhr) {
                    showAlert('Fehler beim Neustarten des Workers: ' + xhr.responseJSON?.message, 'danger');
                });
        }
        
        function deleteWorker(workerId) {
            if (confirm('Sind Sie sicher, dass Sie diesen Worker löschen möchten?')) {
                const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
                $.ajax({
                    url: `/${baseUrl}/workers/${workerId}`,
                    type: 'DELETE'
                })
                .done(function(response) {
                    showAlert('Worker erfolgreich gelöscht', 'success');
                    setTimeout(refreshData, 1000);
                })
                .fail(function(xhr) {
                    showAlert('Fehler beim Löschen des Workers: ' + xhr.responseJSON?.message, 'danger');
                });
            }
        }
        
        // Queue Management Functions
        function startQueueWorkers(queueId) {
            const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
            $.post(`/${baseUrl}/queues/${queueId}/start-workers`)
                .done(function(response) {
                    showAlert('Queue-Worker erfolgreich gestartet', 'success');
                    setTimeout(refreshData, 1000);
                })
                .fail(function(xhr) {
                    showAlert('Fehler beim Starten der Queue-Worker: ' + xhr.responseJSON?.message, 'danger');
                });
        }
        
        function stopQueueWorkers(queueId) {
            const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
            $.post(`/${baseUrl}/queues/${queueId}/stop-workers`)
                .done(function(response) {
                    showAlert('Queue-Worker erfolgreich gestoppt', 'success');
                    setTimeout(refreshData, 1000);
                })
                .fail(function(xhr) {
                    showAlert('Fehler beim Stoppen der Queue-Worker: ' + xhr.responseJSON?.message, 'danger');
                });
        }
        
        function clearQueueJobs(queueId) {
            if (confirm('Sind Sie sicher, dass Sie alle Jobs in dieser Queue löschen möchten?')) {
                const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
                $.post(`/${baseUrl}/queues/${queueId}/clear-jobs`)
                    .done(function(response) {
                        showAlert('Queue-Jobs erfolgreich gelöscht', 'success');
                        setTimeout(refreshData, 1000);
                    })
                    .fail(function(xhr) {
                        showAlert('Fehler beim Löschen der Queue-Jobs: ' + xhr.responseJSON?.message, 'danger');
                    });
            }
        }
        
        function clearQueueFailedJobs(queueId) {
            if (confirm('Sind Sie sicher, dass Sie alle fehlgeschlagenen Jobs in dieser Queue löschen möchten?')) {
                const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
                $.post(`/${baseUrl}/queues/${queueId}/clear-failed`)
                    .done(function(response) {
                        showAlert('Fehlgeschlagene Jobs erfolgreich gelöscht', 'success');
                        setTimeout(refreshData, 1000);
                    })
                    .fail(function(xhr) {
                        showAlert('Fehler beim Löschen der fehlgeschlagenen Jobs: ' + xhr.responseJSON?.message, 'danger');
                    });
            }
        }
        
        // Alert Function
        function showAlert(message, type = 'info') {
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
            
            // Remove existing alerts
            $('.alert').remove();
            
            // Add new alert at the top of the container
            $('.container-fluid').prepend(alertHtml);
            
            // Auto-remove after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut();
            }, 5000);
        }
    </script>
    
    @stack('scripts')
</body>
</html>