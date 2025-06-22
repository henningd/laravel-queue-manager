<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Laravel') }} - Queue Manager</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Heroicons -->
    <script src="https://unpkg.com/@heroicons/react@2.0.18/24/outline/index.js" type="module"></script>
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-gray-50" x-data="{ sidebarOpen: false }">
    <!-- Mobile sidebar -->
    <div x-show="sidebarOpen" class="relative z-50 lg:hidden" x-cloak>
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/80"></div>
        
        <div class="fixed inset-0 flex">
            <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="relative mr-16 flex w-full max-w-xs flex-1">
                <div x-show="sidebarOpen" x-transition:enter="ease-in-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in-out duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="absolute left-full top-0 flex w-16 justify-center pt-5">
                    <button type="button" class="-m-2.5 p-2.5" @click="sidebarOpen = false">
                        <span class="sr-only">Close sidebar</span>
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-2">
                    <div class="flex h-16 shrink-0 items-center">
                        <div class="flex items-center">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-600">
                                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                </svg>
                            </div>
                            <span class="ml-3 text-xl font-semibold text-gray-900">Queue Manager</span>
                        </div>
                    </div>
                    <nav class="flex flex-1 flex-col">
                        <ul role="list" class="flex flex-1 flex-col gap-y-7">
                            <li>
                                <ul role="list" class="-mx-2 space-y-1">
                                    <li>
                                        <a href="{{ route('queue-manager.dashboard') }}" class="bg-gray-50 text-primary-700 group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                                            <svg class="h-6 w-6 shrink-0 text-primary-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                            </svg>
                                            Dashboard
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- Static sidebar for desktop -->
    <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-72 lg:flex-col">
        <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-white px-6">
            <div class="flex h-16 shrink-0 items-center">
                <div class="flex items-center">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-600">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                        </svg>
                    </div>
                    <span class="ml-3 text-xl font-semibold text-gray-900">Queue Manager</span>
                </div>
            </div>
            <nav class="flex flex-1 flex-col">
                <ul role="list" class="flex flex-1 flex-col gap-y-7">
                    <li>
                        <ul role="list" class="-mx-2 space-y-1">
                            <li>
                                <a href="{{ route('queue-manager.dashboard') }}" class="bg-gray-50 text-primary-700 group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold">
                                    <svg class="h-6 w-6 shrink-0 text-primary-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                    </svg>
                                    Dashboard
                                </a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="lg:pl-72">
        <!-- Top navigation -->
        <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
            <button type="button" class="-m-2.5 p-2.5 text-gray-700 lg:hidden" @click="sidebarOpen = true">
                <span class="sr-only">Open sidebar</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>

            <div class="h-6 w-px bg-gray-200 lg:hidden"></div>

            <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                <div class="flex items-center gap-x-4 lg:gap-x-6">
                    <div class="flex items-center gap-x-2">
                        <div class="h-2 w-2 rounded-full bg-green-400"></div>
                        <span class="text-sm text-gray-500">System läuft</span>
                    </div>
                </div>
                <div class="flex items-center gap-x-4 lg:gap-x-6">
                    <div class="flex items-center gap-x-2 text-sm text-gray-500">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="current-time">{{ now()->format('H:i:s') }}</span>
                    </div>
                    <button onclick="refreshData()" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <main class="py-8">
            <div class="px-4 sm:px-6 lg:px-8">
                @yield('content')
            </div>
        </main>
    </div>

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
            const alertTypes = {
                'success': 'bg-green-50 text-green-800 border-green-200',
                'danger': 'bg-red-50 text-red-800 border-red-200',
                'warning': 'bg-yellow-50 text-yellow-800 border-yellow-200',
                'info': 'bg-blue-50 text-blue-800 border-blue-200'
            };
            
            const alertHtml = `
                <div class="fixed top-4 right-4 z-50 max-w-sm w-full">
                    <div class="rounded-md border p-4 ${alertTypes[type] || alertTypes.info}">
                        <div class="flex">
                            <div class="ml-3">
                                <p class="text-sm font-medium">${message}</p>
                            </div>
                            <div class="ml-auto pl-3">
                                <button type="button" class="inline-flex rounded-md p-1.5 hover:bg-gray-100" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove existing alerts
            document.querySelectorAll('.fixed.top-4.right-4').forEach(el => el.remove());
            
            // Add new alert
            document.body.insertAdjacentHTML('beforeend', alertHtml);
            
            // Auto-remove after 5 seconds
            setTimeout(function() {
                document.querySelectorAll('.fixed.top-4.right-4').forEach(el => el.remove());
            }, 5000);
        }
    </script>
    
    @stack('scripts')
</body>
</html>