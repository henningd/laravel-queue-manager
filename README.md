# Laravel Queue Manager

Ein umfassendes Laravel-Package für die Verwaltung und Überwachung von Queues und Workern mit einem benutzerfreundlichen Dashboard.

## Features

- 🎛️ **Web-Dashboard** - Intuitive Benutzeroberfläche zur Verwaltung von Queues und Workern
- 👷 **Worker-Management** - Erstellen, starten, stoppen und überwachen von Queue-Workern
- 📋 **Queue-Konfiguration** - Flexible Konfiguration von Queues mit Prioritäten und Rate-Limiting
- 📊 **Real-time Monitoring** - Live-Status und Performance-Metriken
- 🔧 **Console Commands** - Umfangreiche CLI-Tools für die Verwaltung
- 🚀 **Auto-Scaling** - Automatische Worker-Skalierung basierend auf Queue-Load
- 🔒 **Security** - Konfigurierbare Middleware und Zugriffskontrollen
- 🌐 **Cross-Platform** - Unterstützung für Windows und Linux

## Installation

> ⚠️ **Wichtiger Hinweis:** Dieses Package ist noch nicht auf Packagist veröffentlicht.
>
> Für detaillierte Installationsanweisungen siehe:
> - [`INSTALLATION.md`](INSTALLATION.md) - Vollständige Installationsanleitung
> - [`LOCAL_INSTALLATION.md`](LOCAL_INSTALLATION.md) - Lokale Installation für Entwicklung
> - [`PUBLISHING.md`](PUBLISHING.md) - Anweisungen zur Veröffentlichung auf Packagist

### Schnelle lokale Installation

Für Entwicklung und Testing können Sie das Package direkt über Git installieren:

1. **Repository in composer.json hinzufügen:**
```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/henningd/laravel-queue-manager.git"
        }
    ],
    "require": {
        "henningd/laravel-queue-manager": "dev-main"
    }
}
```

2. **Package installieren:**
```bash
composer install
```

3. **Package konfigurieren:**
```bash
php artisan queue-manager:install
```

### Nach Veröffentlichung auf Packagist

```bash
# Standard-Installation (verfügbar nach Veröffentlichung)
composer require henningd/laravel-queue-manager
php artisan queue-manager:install
```

### Manuelle Installation

Falls Sie die Installation Schritt für Schritt durchführen möchten:

```bash
# Konfiguration publizieren
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider" --tag="config"

# Migrationen publizieren
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider" --tag="migrations"

# Views publizieren (optional)
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider" --tag="views"

# Migrationen ausführen
php artisan migrate

# Standard-Konfigurationen erstellen
php artisan queue-manager:seed
```

## Konfiguration

Die Hauptkonfiguration befindet sich in `config/queue-manager.php`:

```php
return [
    // Dashboard-Einstellungen
    'dashboard' => [
        'enabled' => true,
        'auto_refresh' => true,
        'refresh_interval' => 30, // Sekunden
    ],

    // Route-Konfiguration
    'route' => [
        'prefix' => 'queue-manager',
        'middleware' => ['web'],
        'name' => 'queue-manager.',
    ],

    // Worker-Einstellungen
    'workers' => [
        'default_timeout' => 60,
        'default_memory' => 128,
        'default_sleep' => 3,
        'max_workers_per_queue' => 10,
    ],

    // Queue-Einstellungen
    'queues' => [
        'auto_discovery' => true,
        'default_priority' => 1,
        'rate_limiting' => true,
    ],

    // Security-Einstellungen
    'security' => [
        'enabled' => true,
        'allowed_ips' => [],
        'require_auth' => false,
    ],
];
```

## Verwendung

### Dashboard

Nach der Installation können Sie das Dashboard unter `/queue-manager` aufrufen:

```
http://your-app.com/queue-manager
```

### Console Commands

#### Worker-Management

```bash
# Worker erstellen
php artisan queue-manager:worker:create "My Worker" --queue=default --timeout=60 --start

# Worker auflisten
php artisan queue-manager:worker:list

# Worker nach Queue filtern
php artisan queue-manager:worker:list --queue=emails

# Worker nach Status filtern
php artisan queue-manager:worker:list --status=running
```

#### Queue-Management

```bash
# Standard-Konfigurationen erstellen
php artisan queue-manager:seed

# Package installieren
php artisan queue-manager:install
```

### Programmatische Verwendung

#### Worker erstellen und verwalten

```php
use HenningD\LaravelQueueManager\Models\QueueWorker;

// Worker erstellen
$worker = QueueWorker::create([
    'name' => 'Email Worker',
    'queue' => 'emails',
    'timeout' => 60,
    'memory' => 128,
    'auto_start' => true,
]);

// Worker starten
$worker->start();

// Worker stoppen
$worker->stop();

// Worker neustarten
$worker->restart();

// Worker-Status prüfen
if ($worker->isRunning()) {
    echo "Worker läuft";
}
```

#### Queue-Konfiguration

```php
use HenningD\LaravelQueueManager\Models\QueueConfiguration;

// Queue-Konfiguration erstellen
$queue = QueueConfiguration::create([
    'name' => 'high-priority',
    'description' => 'Hochpriorisierte Jobs',
    'connection' => 'redis',
    'priority' => 10,
    'max_jobs_per_minute' => 120,
    'max_workers' => 5,
    'auto_scale' => true,
]);

// Queue aktivieren/deaktivieren
$queue->activate();
$queue->deactivate();
```

### API-Endpunkte

Das Package stellt verschiedene API-Endpunkte zur Verfügung:

```bash
# Status abrufen
GET /queue-manager/status

# Worker verwalten
GET /queue-manager/workers
POST /queue-manager/workers
PUT /queue-manager/workers/{id}
DELETE /queue-manager/workers/{id}
POST /queue-manager/workers/{id}/start
POST /queue-manager/workers/{id}/stop
POST /queue-manager/workers/{id}/restart

# Queues verwalten
GET /queue-manager/queues
POST /queue-manager/queues
PUT /queue-manager/queues/{id}
DELETE /queue-manager/queues/{id}
```

## Erweiterte Features

### Auto-Scaling

Aktivieren Sie Auto-Scaling für automatische Worker-Skalierung:

```php
$queue = QueueConfiguration::create([
    'name' => 'auto-scaled-queue',
    'auto_scale' => true,
    'min_workers' => 1,
    'max_workers' => 10,
    'scale_up_threshold' => 10, // Jobs in Queue
    'scale_down_threshold' => 2,
]);
```

### Rate Limiting

Konfigurieren Sie Rate-Limiting für Queues:

```php
$queue = QueueConfiguration::create([
    'name' => 'rate-limited-queue',
    'max_jobs_per_minute' => 60,
    'rate_limiting_enabled' => true,
]);
```

### Custom Middleware

Fügen Sie eigene Middleware für das Dashboard hinzu:

```php
// config/queue-manager.php
'route' => [
    'middleware' => ['web', 'auth', 'admin'],
],
```

### Security

Beschränken Sie den Zugriff auf bestimmte IP-Adressen:

```php
// config/queue-manager.php
'security' => [
    'enabled' => true,
    'allowed_ips' => ['127.0.0.1', '192.168.1.0/24'],
    'require_auth' => true,
],
```

## Systemanforderungen

- PHP 8.1 oder höher
- Laravel 10.0 oder höher
- Unterstützte Queue-Treiber: database, redis, sync
- Für Worker-Management: proc_open() und proc_get_status() Funktionen

## Troubleshooting

### Worker starten nicht

1. Überprüfen Sie die PHP-Konfiguration:
```bash
php -m | grep proc
```

2. Stellen Sie sicher, dass `proc_open` und `proc_get_status` verfügbar sind

3. Überprüfen Sie die Berechtigungen für das Laravel-Verzeichnis

### Dashboard nicht erreichbar

1. Überprüfen Sie die Route-Konfiguration in `config/queue-manager.php`
2. Stellen Sie sicher, dass die Middleware korrekt konfiguriert ist
3. Leeren Sie den Route-Cache: `php artisan route:clear`

### Migrationen schlagen fehl

1. Überprüfen Sie die Datenbankverbindung
2. Stellen Sie sicher, dass die Migrations-Tabelle existiert
3. Führen Sie die Migrationen manuell aus: `php artisan migrate`

## Contributing

Beiträge sind willkommen! Bitte erstellen Sie einen Pull Request oder öffnen Sie ein Issue auf GitHub.

## License

Dieses Package ist unter der MIT-Lizenz lizenziert. Siehe [LICENSE](LICENSE) für Details.

## Support

- GitHub Issues: [https://github.com/henningd/laravel-queue-manager/issues](https://github.com/henningd/laravel-queue-manager/issues)
- Dokumentation: [https://github.com/henningd/laravel-queue-manager](https://github.com/henningd/laravel-queue-manager)

## Changelog

### v1.0.0
- Initiale Veröffentlichung
- Worker-Management
- Queue-Konfiguration
- Web-Dashboard
- Console Commands
- Auto-Scaling
- Rate Limiting