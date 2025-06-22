@extends('queue-manager::layout')

@section('content')
<div class="space-y-8">
    <!-- Header -->
    <div class="md:flex md:items-center md:justify-between">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">
                Queue Manager Dashboard
            </h2>
            <p class="mt-1 text-sm text-gray-500">
                Überwachung und Verwaltung von Laravel Queues und Workern
            </p>
        </div>
        <div class="mt-4 flex md:ml-4 md:mt-0">
            <button type="button" onclick="refreshData()" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                <svg class="-ml-0.5 mr-1.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                Aktualisieren
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Queues -->
        <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
            <dt>
                <div class="absolute rounded-md bg-indigo-500 p-3">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                    </svg>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500">Konfigurierte Queues</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_queues'] ?? 0 }}</p>
                <p class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                    {{ $stats['active_queues'] ?? 0 }} aktiv
                </p>
            </dd>
        </div>

        <!-- Total Workers -->
        <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
            <dt>
                <div class="absolute rounded-md bg-green-500 p-3">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500">Worker Prozesse</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_workers'] ?? 0 }}</p>
                <p class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
                    {{ $stats['running_workers'] ?? 0 }} laufend
                </p>
            </dd>
        </div>

        <!-- Total Jobs -->
        <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
            <dt>
                <div class="absolute rounded-md bg-yellow-500 p-3">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 00.75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 00-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0112 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 01-.673-.38m0 0A2.18 2.18 0 013 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 013.413-.387m7.5 0V5.25A2.25 2.25 0 0013.5 3h-3a2.25 2.25 0 00-2.25 2.25v.894m7.5 0a48.667 48.667 0 00-7.5 0M12 12.75h.008v.008H12v-.008z" />
                    </svg>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500">Jobs in Warteschlange</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                <p class="text-2xl font-semibold text-gray-900">{{ $stats['total_jobs'] ?? 0 }}</p>
                @if(($stats['failed_jobs'] ?? 0) > 0)
                    <p class="ml-2 flex items-baseline text-sm font-semibold text-red-600">
                        {{ $stats['failed_jobs'] }} fehlgeschlagen
                    </p>
                @endif
            </dd>
        </div>

        <!-- System Status -->
        <div class="relative overflow-hidden rounded-lg bg-white px-4 pb-12 pt-5 shadow sm:px-6 sm:pt-6">
            <dt>
                <div class="absolute rounded-md bg-blue-500 p-3">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3V6a3 3 0 013-3h13.5a3 3 0 013 3v5.25a3 3 0 01-3 3m-16.5 0a3 3 0 013 3v6.75a3 3 0 01-3 3h13.5a3 3 0 01-3-3v-6.75a3 3 0 013-3m-16.5 0h16.5" />
                    </svg>
                </div>
                <p class="ml-16 truncate text-sm font-medium text-gray-500">System Status</p>
            </dt>
            <dd class="ml-16 flex items-baseline pb-6 sm:pb-7">
                <p class="text-2xl font-semibold text-green-600">Online</p>
                <div class="ml-2 flex items-center">
                    <div class="h-2 w-2 rounded-full bg-green-400"></div>
                </div>
            </dd>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="rounded-lg bg-white shadow">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900">Schnellaktionen</h3>
            <div class="mt-5">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <button onclick="startAllWorkers()" class="inline-flex items-center justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                        </svg>
                        Alle Worker starten
                    </button>
                    <button onclick="restartAllWorkers()" class="inline-flex items-center justify-center rounded-md bg-yellow-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-yellow-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-yellow-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Alle Worker neustarten
                    </button>
                    <button onclick="stopAllWorkers()" class="inline-flex items-center justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 017.5 5.25h9a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25v-9z" />
                        </svg>
                        Alle Worker stoppen
                    </button>
                    <button onclick="retryFailedJobs()" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        Failed Jobs wiederholen
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Workers Section -->
    <div class="rounded-lg bg-white shadow">
        <div class="px-4 py-5 sm:p-6">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">Worker Prozesse</h3>
                    <p class="mt-2 text-sm text-gray-700">Verwaltung und Überwachung aller Queue-Worker</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <button type="button" onclick="openAddWorkerModal()" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Worker hinzufügen
                    </button>
                </div>
            </div>
            
            @if($workers->isEmpty())
                <div class="mt-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">Keine Worker</h3>
                    <p class="mt-1 text-sm text-gray-500">Erstellen Sie Ihren ersten Worker, um zu beginnen.</p>
                    <div class="mt-6">
                        <button type="button" onclick="openAddWorkerModal()" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Neuen Worker erstellen
                        </button>
                    </div>
                </div>
            @else
                <div class="mt-6 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($workers as $worker)
                        <div class="col-span-1 divide-y divide-gray-200 rounded-lg bg-white border border-gray-200 shadow-sm">
                            <div class="flex w-full items-center justify-between space-x-6 p-6">
                                <div class="flex-1 truncate">
                                    <div class="flex items-center space-x-3">
                                        <h3 class="truncate text-sm font-medium text-gray-900">{{ $worker->name }}</h3>
                                        @if($worker->is_running ?? false)
                                            <span class="inline-flex flex-shrink-0 items-center rounded-full bg-green-50 px-1.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                                Läuft
                                            </span>
                                        @else
                                            <span class="inline-flex flex-shrink-0 items-center rounded-full bg-gray-50 px-1.5 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                                Gestoppt
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-1 truncate text-sm text-gray-500">Queue: {{ $worker->queue }}</p>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Timeout: {{ $worker->timeout }}s | Memory: {{ $worker->memory }}MB
                                    </p>
                                    @if($worker->is_running ?? false)
                                        <p class="mt-1 text-xs text-gray-400">
                                            Gestartet: {{ $worker->started_at ? $worker->started_at->diffForHumans() : 'Unbekannt' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <div class="-mt-px flex divide-x divide-gray-200">
                                    @if($worker->is_running ?? false)
                                        <div class="flex w-0 flex-1">
                                            <button onclick="stopWorker({{ $worker->id }})" class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-bl-lg border border-transparent py-4 text-sm font-semibold text-yellow-600 hover:text-yellow-500">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 017.5 5.25h9a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25v-9z" />
                                                </svg>
                                                Stoppen
                                            </button>
                                        </div>
                                        <div class="-ml-px flex w-0 flex-1">
                                            <button onclick="restartWorker({{ $worker->id }})" class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold text-blue-600 hover:text-blue-500">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                </svg>
                                                Neustarten
                                            </button>
                                        </div>
                                    @else
                                        <div class="flex w-0 flex-1">
                                            <button onclick="startWorker({{ $worker->id }})" class="relative -mr-px inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-bl-lg border border-transparent py-4 text-sm font-semibold text-green-600 hover:text-green-500">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                                                </svg>
                                                Starten
                                            </button>
                                        </div>
                                        <div class="-ml-px flex w-0 flex-1">
                                            <button onclick="deleteWorker({{ $worker->id }})" class="relative inline-flex w-0 flex-1 items-center justify-center gap-x-3 rounded-br-lg border border-transparent py-4 text-sm font-semibold text-red-600 hover:text-red-500">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                                Löschen
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Queues Section -->
    <div class="rounded-lg bg-white shadow">
        <div class="px-4 py-5 sm:p-6">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">Queue Konfigurationen</h3>
                    <p class="mt-2 text-sm text-gray-700">Verwaltung und Konfiguration aller Queues</p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <button type="button" onclick="openAddQueueModal()" class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Queue hinzufügen
                    </button>
                </div>
            </div>
            
            @if($queues->isEmpty())
                <div class="mt-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">Keine Queues</h3>
                    <p class="mt-1 text-sm text-gray-500">Erstellen Sie Ihre erste Queue-Konfiguration.</p>
                    <div class="mt-6">
                        <button type="button" onclick="openAddQueueModal()" class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            Neue Queue erstellen
                        </button>
                    </div>
                </div>
            @else
                <div class="mt-6 overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Priorität</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Jobs</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wide text-gray-500">Status</th>
                                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Aktionen</span></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($queues as $queue)
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ $queue->name }}</div>
                                            @if($queue->description)
                                                <div class="text-gray-500">{{ $queue->description }}</div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        <span class="inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                            {{ $queue->priority }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        <div class="flex space-x-2">
                                            <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                                {{ $queue->pending_jobs ?? 0 }} wartend
                                            </span>
                                            @if(($queue->failed_jobs ?? 0) > 0)
                                                <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/10">
                                                    {{ $queue->failed_jobs }} fehlgeschlagen
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        @if($queue->is_active)
                                            <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">
                                                Aktiv
                                            </span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                                Inaktiv
                                            </span>
                                        @endif
                                    </td>
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <div class="flex justify-end space-x-2">
                                            <button onclick="startQueueWorkers({{ $queue->id }})" class="text-green-600 hover:text-green-900" title="Worker starten">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
                                                </svg>
                                            </button>
                                            <button onclick="stopQueueWorkers({{ $queue->id }})" class="text-yellow-600 hover:text-yellow-900" title="Worker stoppen">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 017.5 5.25h9a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25v-9z" />
                                                </svg>
                                            </button>
                                            <button onclick="clearQueueJobs({{ $queue->id }})" class="text-blue-600 hover:text-blue-900" title="Jobs löschen">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                                </svg>
                                            </button>
                                            <button onclick="clearQueueFailedJobs({{ $queue->id }})" class="text-red-600 hover:text-red-900" title="Failed Jobs löschen">
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
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

    <!-- System Information -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- System Info -->
        <div class="rounded-lg bg-white shadow">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900">System Information</h3>
                <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-base font-normal text-gray-900">Laravel Version</dt>
                        <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                            <div class="flex items-baseline text-2xl font-semibold text-indigo-600">
                                {{ app()->version() }}
                            </div>
                        </dd>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-base font-normal text-gray-900">PHP Version</dt>
                        <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                            <div class="flex items-baseline text-2xl font-semibold text-indigo-600">
                                {{ PHP_VERSION }}
                            </div>
                        </dd>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-base font-normal text-gray-900">Queue Driver</dt>
                        <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                            <div class="flex items-baseline text-2xl font-semibold text-indigo-600">
                                {{ config('queue.default') }}
                            </div>
                        </dd>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-base font-normal text-gray-900">Memory Limit</dt>
                        <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                            <div class="flex items-baseline text-2xl font-semibold text-indigo-600">
                                {{ ini_get('memory_limit') }}
                            </div>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Performance Metrics -->
        <div class="rounded-lg bg-white shadow">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-base font-semibold leading-6 text-gray-900">Performance Metriken</h3>
                <dl class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-base font-normal text-gray-900">Jobs heute</dt>
                        <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                            <div class="flex items-baseline text-2xl font-semibold text-green-600">
                                {{ $stats['jobs_today'] ?? 0 }}
                            </div>
                        </dd>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-base font-normal text-gray-900">Erfolgsrate</dt>
                        <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                            <div class="flex items-baseline text-2xl font-semibold text-green-600">
                                {{ $stats['success_rate'] ?? 'N/A' }}%
                            </div>
                        </dd>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-base font-normal text-gray-900">Ø Job-Zeit</dt>
                        <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                            <div class="flex items-baseline text-2xl font-semibold text-blue-600">
                                {{ $stats['avg_job_time'] ?? 'N/A' }}
                            </div>
                        </dd>
                    </div>
                    <div class="px-4 py-5 sm:p-6">
                        <dt class="text-base font-normal text-gray-900">Letzte Aktualisierung</dt>
                        <dd class="mt-1 flex items-baseline justify-between md:block lg:flex">
                            <div class="flex items-baseline text-2xl font-semibold text-gray-600">
                                {{ now()->format('H:i:s') }}
                            </div>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Dashboard-spezifische Funktionen (Worker/Queue-Management ist bereits im Layout definiert)

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

// Modal Functions
function openAddWorkerModal() {
    showAlert('Worker-Modal wird implementiert', 'info');
}

function openAddQueueModal() {
    showAlert('Queue-Modal wird implementiert', 'info');
}

// Form Submissions
function submitWorkerForm(formData) {
    const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
    $.post(`/${baseUrl}/workers`, formData)
        .done(function(response) {
            showAlert('Worker erfolgreich erstellt', 'success');
            setTimeout(refreshData, 1000);
        })
        .fail(function(xhr) {
            showAlert('Fehler beim Erstellen des Workers: ' + xhr.responseJSON?.message, 'danger');
        });
}

function submitQueueForm(formData) {
    const baseUrl = '{{ config("queue-manager.route.prefix", "queue-manager") }}';
    $.post(`/${baseUrl}/queues`, formData)
        .done(function(response) {
            showAlert('Queue erfolgreich erstellt', 'success');
            setTimeout(refreshData, 1000);
        })
        .fail(function(xhr) {
            showAlert('Fehler beim Erstellen der Queue: ' + xhr.responseJSON?.message, 'danger');
        });
}
</script>
@endpush