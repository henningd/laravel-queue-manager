# Konfiguration

Das Laravel Queue Manager Package bietet umfangreiche Konfigurationsmöglichkeiten, um es an deine spezifischen Anforderungen anzupassen.

## 📁 Konfigurationsdateien

### Hauptkonfiguration
Die zentrale Konfigurationsdatei befindet sich unter:
```
config/queue-manager.php
```

### Umgebungsvariablen
Wichtige Einstellungen können über die `.env` Datei konfiguriert werden:
```env
# Queue Manager Grundeinstellungen
QUEUE_MANAGER_ENABLED=true
QUEUE_MANAGER_PREFIX=queue-manager
QUEUE_MANAGER_MIDDLEWARE=web

# Dashboard-Einstellungen
QUEUE_MANAGER_REFRESH_INTERVAL=5000
QUEUE_MANAGER_ITEMS_PER_PAGE=25
QUEUE_MANAGER_AUTO_REFRESH=true

# API-Einstellungen
QUEUE_MANAGER_API_ENABLED=true
QUEUE_MANAGER_API_MIDDLEWARE=api
QUEUE_MANAGER_API_RATE_LIMIT=60

# Sicherheit
QUEUE_MANAGER_AUTH_REQUIRED=false
QUEUE_MANAGER_ALLOWED_IPS=
QUEUE_MANAGER_DEBUG=false
```

## ⚙️ Grundkonfiguration

### Basis-Einstellungen
```php
<?php
// config/queue-manager.php

return [
    /*
    |--------------------------------------------------------------------------
    | Queue Manager aktiviert
    |--------------------------------------------------------------------------
    |
    | Bestimmt, ob das Queue Manager Package aktiviert ist.
    |
    */
    'enabled' => env('QUEUE_MANAGER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Route-Präfix
    |--------------------------------------------------------------------------
    |
    | Das URL-Präfix für alle Queue Manager Routen.
    | Standard: /queue-manager
    |
    */
    'prefix' => env('QUEUE_MANAGER_PREFIX', 'queue-manager'),

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware-Gruppen, die auf alle Routen angewendet werden.
    |
    */
    'middleware' => [
        'web',
        // 'auth', // Für authentifizierte Zugriffe
        // 'admin', // Für Admin-Bereiche
    ],

    /*
    |--------------------------------------------------------------------------
    | Route-Namen-Präfix
    |--------------------------------------------------------------------------
    |
    | Präfix für alle Route-Namen.
    |
    */
    'route_name_prefix' => 'queue-manager',
];
```

## 🎛️ Dashboard-Konfiguration

### Dashboard-Einstellungen
```php
'dashboard' => [
    /*
    |--------------------------------------------------------------------------
    | Auto-Refresh
    |--------------------------------------------------------------------------
    |
    | Automatische Aktualisierung des Dashboards aktivieren.
    |
    */
    'auto_refresh' => env('QUEUE_MANAGER_AUTO_REFRESH', true),

    /*
    |--------------------------------------------------------------------------
    | Refresh-Intervall
    |--------------------------------------------------------------------------
    |
    | Intervall für automatische Updates in Millisekunden.
    |
    */
    'refresh_interval' => env('QUEUE_MANAGER_REFRESH_INTERVAL', 5000),

    /*
    |--------------------------------------------------------------------------
    | Items pro Seite
    |--------------------------------------------------------------------------
    |
    | Anzahl der Items, die pro Seite angezeigt werden.
    |
    */
    'items_per_page' => env('QUEUE_MANAGER_ITEMS_PER_PAGE', 25),

    /*
    |--------------------------------------------------------------------------
    | Statistiken anzeigen
    |--------------------------------------------------------------------------
    |
    | Bestimmt, ob Statistik-Karten angezeigt werden.
    |
    */
    'show_statistics' => true,

    /*
    |--------------------------------------------------------------------------
    | Zeitformat
    |--------------------------------------------------------------------------
    |
    | Format für Zeitangaben im Dashboard.
    |
    */
    'date_format' => 'd.m.Y H:i:s',

    /*
    |--------------------------------------------------------------------------
    | Timezone
    |--------------------------------------------------------------------------
    |
    | Zeitzone für Anzeigen (null = App-Timezone verwenden).
    |
    */
    'timezone' => null,

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    |
    | Dashboard-Theme: 'light', 'dark', 'auto'
    |
    */
    'theme' => 'light',

    /*
    |--------------------------------------------------------------------------
    | Sprache
    |--------------------------------------------------------------------------
    |
    | Dashboard-Sprache: 'de', 'en'
    |
    */
    'locale' => 'de',
],
```

### UI-Anpassungen
```php
'ui' => [
    /*
    |--------------------------------------------------------------------------
    | Sidebar-Konfiguration
    |--------------------------------------------------------------------------
    */
    'sidebar' => [
        'collapsed_by_default' => false,
        'show_icons' => true,
        'show_labels' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tabellen-Konfiguration
    |--------------------------------------------------------------------------
    */
    'tables' => [
        'striped' => true,
        'hover' => true,
        'responsive' => true,
        'compact' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Farb-Schema
    |--------------------------------------------------------------------------
    */
    'colors' => [
        'primary' => '#3B82F6',
        'success' => '#10B981',
        'warning' => '#F59E0B',
        'danger' => '#EF4444',
        'info' => '#06B6D4',
    ],
],
```

## 🔌 API-Konfiguration

### API-Einstellungen
```php
'api' => [
    /*
    |--------------------------------------------------------------------------
    | API aktiviert
    |--------------------------------------------------------------------------
    |
    | Bestimmt, ob die API-Endpoints verfügbar sind.
    |
    */
    'enabled' => env('QUEUE_MANAGER_API_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | API-Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware für API-Routen.
    |
    */
    'middleware' => [
        'api',
        'throttle:' . env('QUEUE_MANAGER_API_RATE_LIMIT', 60) . ',1',
        // 'auth:sanctum', // Für Token-basierte Auth
    ],

    /*
    |--------------------------------------------------------------------------
    | API-Präfix
    |--------------------------------------------------------------------------
    |
    | URL-Präfix für API-Routen.
    |
    */
    'prefix' => 'api/queue-manager',

    /*
    |--------------------------------------------------------------------------
    | Versionierung
    |--------------------------------------------------------------------------
    |
    | API-Versionierung aktivieren.
    |
    */
    'versioning' => [
        'enabled' => true,
        'default_version' => 'v1',
        'header' => 'Accept-Version',
    ],

    /*
    |--------------------------------------------------------------------------
    | CORS-Einstellungen
    |--------------------------------------------------------------------------
    |
    | Cross-Origin Resource Sharing Konfiguration.
    |
    */
    'cors' => [
        'enabled' => true,
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'],
        'allowed_headers' => ['*'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Response-Format
    |--------------------------------------------------------------------------
    |
    | Standard-Format für API-Responses.
    |
    */
    'response_format' => [
        'include_meta' => true,
        'include_links' => true,
        'wrap_data' => true,
    ],
],
```

### Authentifizierung
```php
'authentication' => [
    /*
    |--------------------------------------------------------------------------
    | Auth-Typ
    |--------------------------------------------------------------------------
    |
    | Authentifizierungstyp: 'none', 'session', 'token', 'sanctum'
    |
    */
    'type' => env('QUEUE_MANAGER_AUTH_TYPE', 'none'),

    /*
    |--------------------------------------------------------------------------
    | Auth-Guard
    |--------------------------------------------------------------------------
    |
    | Laravel Auth Guard für Authentifizierung.
    |
    */
    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | Berechtigungen
    |--------------------------------------------------------------------------
    |
    | Erforderliche Berechtigungen für verschiedene Aktionen.
    |
    */
    'permissions' => [
        'view_dashboard' => null,
        'manage_workers' => 'manage-queues',
        'manage_queues' => 'manage-queues',
        'view_jobs' => null,
        'retry_jobs' => 'manage-queues',
        'delete_jobs' => 'manage-queues',
    ],

    /*
    |--------------------------------------------------------------------------
    | IP-Whitelist
    |--------------------------------------------------------------------------
    |
    | Erlaubte IP-Adressen (leer = alle erlaubt).
    |
    */
    'allowed_ips' => array_filter(explode(',', env('QUEUE_MANAGER_ALLOWED_IPS', ''))),
],
```

## 👷 Worker-Konfiguration

### Worker-Einstellungen
```php
'workers' => [
    /*
    |--------------------------------------------------------------------------
    | Standard-Worker-Konfiguration
    |--------------------------------------------------------------------------
    */
    'default' => [
        'timeout' => 60,
        'memory' => 512,
        'sleep' => 3,
        'tries' => 3,
        'max_jobs' => 1000,
        'max_time' => 3600,
        'backoff' => [1, 5, 10],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue-spezifische Worker
    |--------------------------------------------------------------------------
    */
    'queues' => [
        'high' => [
            'timeout' => 30,
            'memory' => 256,
            'sleep' => 1,
            'tries' => 5,
            'priority' => 10,
        ],
        'emails' => [
            'timeout' => 60,
            'memory' => 256,
            'sleep' => 2,
            'tries' => 3,
            'priority' => 5,
        ],
        'reports' => [
            'timeout' => 300,
            'memory' => 1024,
            'sleep' => 5,
            'tries' => 1,
            'priority' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Worker-Überwachung
    |--------------------------------------------------------------------------
    */
    'monitoring' => [
        'enabled' => true,
        'check_interval' => 30, // Sekunden
        'restart_on_memory_limit' => true,
        'restart_on_timeout' => true,
        'max_restart_attempts' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Supervisor-Integration
    |--------------------------------------------------------------------------
    */
    'supervisor' => [
        'enabled' => false,
        'config_path' => '/etc/supervisor/conf.d/',
        'program_template' => 'laravel-worker',
        'auto_restart' => true,
    ],
],
```

## 📋 Queue-Konfiguration

### Queue-Einstellungen
```php
'queues' => [
    /*
    |--------------------------------------------------------------------------
    | Standard-Queue-Konfiguration
    |--------------------------------------------------------------------------
    */
    'default_connection' => env('QUEUE_CONNECTION', 'database'),

    /*
    |--------------------------------------------------------------------------
    | Queue-Prioritäten
    |--------------------------------------------------------------------------
    */
    'priorities' => [
        'critical' => 100,
        'high' => 50,
        'normal' => 10,
        'low' => 1,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue-Limits
    |--------------------------------------------------------------------------
    */
    'limits' => [
        'max_jobs_per_queue' => 10000,
        'max_failed_jobs' => 1000,
        'cleanup_after_days' => 7,
    ],

    /*
    |--------------------------------------------------------------------------
    | Batch-Verarbeitung
    |--------------------------------------------------------------------------
    */
    'batching' => [
        'enabled' => true,
        'default_batch_size' => 100,
        'max_batch_size' => 1000,
        'batch_timeout' => 3600,
    ],
],
```

## 📊 Monitoring & Logging

### Monitoring-Konfiguration
```php
'monitoring' => [
    /*
    |--------------------------------------------------------------------------
    | Metriken sammeln
    |--------------------------------------------------------------------------
    */
    'metrics' => [
        'enabled' => true,
        'retention_days' => 30,
        'aggregation_interval' => 300, // 5 Minuten
    ],

    /*
    |--------------------------------------------------------------------------
    | Alerts
    |--------------------------------------------------------------------------
    */
    'alerts' => [
        'enabled' => true,
        'channels' => ['mail', 'slack'],
        'thresholds' => [
            'failed_jobs_rate' => 5, // Prozent
            'queue_length' => 1000,
            'worker_memory' => 80, // Prozent
            'response_time' => 30, // Sekunden
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Health Checks
    |--------------------------------------------------------------------------
    */
    'health_checks' => [
        'enabled' => true,
        'interval' => 60, // Sekunden
        'timeout' => 10,
        'checks' => [
            'database' => true,
            'redis' => true,
            'workers' => true,
            'disk_space' => true,
        ],
    ],
],
```

### Logging-Konfiguration
```php
'logging' => [
    /*
    |--------------------------------------------------------------------------
    | Log-Level
    |--------------------------------------------------------------------------
    */
    'level' => env('QUEUE_MANAGER_LOG_LEVEL', 'info'),

    /*
    |--------------------------------------------------------------------------
    | Log-Kanäle
    |--------------------------------------------------------------------------
    */
    'channels' => [
        'default' => env('LOG_CHANNEL', 'stack'),
        'workers' => 'queue-workers',
        'jobs' => 'queue-jobs',
        'api' => 'queue-api',
    ],

    /*
    |--------------------------------------------------------------------------
    | Log-Rotation
    |--------------------------------------------------------------------------
    */
    'rotation' => [
        'enabled' => true,
        'max_files' => 10,
        'max_size' => '10M',
    ],

    /*
    |--------------------------------------------------------------------------
    | Strukturierte Logs
    |--------------------------------------------------------------------------
    */
    'structured' => [
        'enabled' => true,
        'include_context' => true,
        'include_extra' => true,
    ],
],
```

## 🔐 Sicherheitskonfiguration

### Sicherheitseinstellungen
```php
'security' => [
    /*
    |--------------------------------------------------------------------------
    | CSRF-Schutz
    |--------------------------------------------------------------------------
    */
    'csrf' => [
        'enabled' => true,
        'except_routes' => [
            'api/*',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    'rate_limiting' => [
        'web' => '60,1', // 60 Requests pro Minute
        'api' => '100,1', // 100 Requests pro Minute
        'admin' => '30,1', // 30 Requests pro Minute
    ],

    /*
    |--------------------------------------------------------------------------
    | Input-Validierung
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'strict_mode' => true,
        'sanitize_input' => true,
        'max_input_length' => 1000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Verschlüsselung
    |--------------------------------------------------------------------------
    */
    'encryption' => [
        'encrypt_job_payloads' => false,
        'encrypt_sensitive_data' => true,
    ],
],
```

## 🎨 Theme & Styling

### Theme-Konfiguration
```php
'theme' => [
    /*
    |--------------------------------------------------------------------------
    | CSS-Framework
    |--------------------------------------------------------------------------
    */
    'framework' => 'tailwind', // 'tailwind', 'bootstrap'

    /*
    |--------------------------------------------------------------------------
    | Custom CSS
    |--------------------------------------------------------------------------
    */
    'custom_css' => [
        'enabled' => false,
        'path' => 'css/queue-manager-custom.css',
    ],

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    */
    'favicon' => '/favicon.ico',

    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    */
    'logo' => [
        'enabled' => true,
        'path' => '/images/logo.png',
        'alt' => 'Queue Manager',
        'width' => 150,
        'height' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Layout-Optionen
    |--------------------------------------------------------------------------
    */
    'layout' => [
        'sidebar_width' => '250px',
        'header_height' => '60px',
        'footer_enabled' => true,
        'breadcrumbs_enabled' => true,
    ],
],
```

## 🔧 Erweiterte Konfiguration

### Performance-Optimierung
```php
'performance' => [
    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'enabled' => true,
        'driver' => env('CACHE_DRIVER', 'redis'),
        'ttl' => 300, // 5 Minuten
        'prefix' => 'queue_manager',
    ],

    /*
    |--------------------------------------------------------------------------
    | Database-Optimierung
    |--------------------------------------------------------------------------
    */
    'database' => [
        'connection' => env('DB_CONNECTION', 'mysql'),
        'chunk_size' => 1000,
        'index_optimization' => true,
        'query_timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Memory-Management
    |--------------------------------------------------------------------------
    */
    'memory' => [
        'gc_enabled' => true,
        'gc_probability' => 1,
        'gc_divisor' => 100,
        'memory_limit' => '512M',
    ],
],
```

### Integration-Optionen
```php
'integrations' => [
    /*
    |--------------------------------------------------------------------------
    | Horizon-Integration
    |--------------------------------------------------------------------------
    */
    'horizon' => [
        'enabled' => false,
        'dashboard_url' => '/horizon',
    ],

    /*
    |--------------------------------------------------------------------------
    | Telescope-Integration
    |--------------------------------------------------------------------------
    */
    'telescope' => [
        'enabled' => false,
        'dashboard_url' => '/telescope',
    ],

    /*
    |--------------------------------------------------------------------------
    | Nova-Integration
    |--------------------------------------------------------------------------
    */
    'nova' => [
        'enabled' => false,
        'resource_class' => 'App\\Nova\\QueueJob',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    */
    'webhooks' => [
        'enabled' => false,
        'endpoints' => [],
        'events' => [
            'job.completed',
            'job.failed',
            'worker.started',
            'worker.stopped',
        ],
    ],
],
```

## 📝 Konfiguration publizieren

### Konfigurationsdatei publizieren
```bash
# Alle Konfigurationsdateien
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider"

# Nur Konfiguration
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider" --tag="config"

# Konfiguration überschreiben
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider" --tag="config" --force
```

### Konfiguration validieren
```bash
# Konfiguration anzeigen
php artisan config:show queue-manager

# Konfiguration cachen
php artisan config:cache

# Cache leeren
php artisan config:clear
```

## ➡️ Nächste Schritte

- [Dashboard Übersicht](Dashboard-Übersicht.md) - Dashboard verwenden
- [Worker Management](Worker-Management.md) - Workers konfigurieren
- [API Endpoints](API-Endpoints.md) - API nutzen
- [Anpassungen](Anpassungen.md) - Package erweitern