# Installation Guide - Laravel Queue Manager

## Schnellinstallation

### 1. Package installieren
```bash
composer require henningd/laravel-queue-manager
```

### 2. Package konfigurieren
```bash
php artisan queue-manager:install
```

Das war's! Das Package ist jetzt installiert und einsatzbereit.

## Manuelle Installation

Falls Sie die Installation Schritt für Schritt durchführen möchten:

### 1. Composer Installation
```bash
composer require henningd/laravel-queue-manager
```

### 2. Service Provider registrieren (Laravel < 5.5)
Fügen Sie den Service Provider in `config/app.php` hinzu:
```php
'providers' => [
    // ...
    HenningD\LaravelQueueManager\QueueManagerServiceProvider::class,
],
```

### 3. Konfiguration publizieren
```bash
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider" --tag="config"
```

### 4. Migrationen publizieren und ausführen
```bash
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider" --tag="migrations"
php artisan migrate
```

### 5. Views publizieren (optional)
```bash
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider" --tag="views"
```

### 6. Standard-Konfigurationen erstellen
```bash
php artisan queue-manager:seed
```

## Erste Schritte

### 1. Dashboard aufrufen
Besuchen Sie: `http://your-app.com/queue-manager`

### 2. Ersten Worker erstellen
```bash
php artisan queue-manager:worker:create "My First Worker" --queue=default --start
```

### 3. Worker-Status prüfen
```bash
php artisan queue-manager:worker:list
```

## Konfiguration

### Basis-Konfiguration
Bearbeiten Sie `config/queue-manager.php`:

```php
return [
    'dashboard' => [
        'enabled' => true,
        'auto_refresh' => true,
        'refresh_interval' => 30,
    ],
    
    'route' => [
        'prefix' => 'queue-manager',
        'middleware' => ['web'],
    ],
    
    'workers' => [
        'default_timeout' => 60,
        'default_memory' => 128,
        'max_workers_per_queue' => 10,
    ],
];
```

### Security-Konfiguration
```php
'security' => [
    'enabled' => true,
    'allowed_ips' => ['127.0.0.1'],
    'require_auth' => true,
],

'route' => [
    'middleware' => ['web', 'auth', 'admin'],
],
```

## Verfügbare Commands

### Installation & Setup
```bash
php artisan queue-manager:install          # Package installieren
php artisan queue-manager:seed             # Standard-Konfigurationen erstellen
```

### Worker-Management
```bash
php artisan queue-manager:worker:create    # Worker erstellen
php artisan queue-manager:worker:list      # Worker auflisten
php artisan queue-manager:worker:start     # Worker starten
```

### Beispiele
```bash
# Worker erstellen und sofort starten
php artisan queue-manager:worker:create "Email Worker" --queue=emails --timeout=30 --start

# Alle Worker auflisten
php artisan queue-manager:worker:list

# Worker für bestimmte Queue auflisten
php artisan queue-manager:worker:list --queue=emails

# Nur laufende Worker anzeigen
php artisan queue-manager:worker:list --status=running

# Alle Worker starten
php artisan queue-manager:worker:start --all

# Worker für bestimmte Queue starten
php artisan queue-manager:worker:start --queue=emails
```

## Troubleshooting

### Problem: Worker starten nicht
**Lösung:**
1. Überprüfen Sie PHP-Funktionen:
```bash
php -r "echo function_exists('proc_open') ? 'OK' : 'FEHLT';"
```

2. Überprüfen Sie Berechtigungen:
```bash
ls -la storage/
chmod -R 755 storage/
```

### Problem: Dashboard nicht erreichbar
**Lösung:**
1. Route-Cache leeren:
```bash
php artisan route:clear
php artisan config:clear
```

2. Middleware überprüfen in `config/queue-manager.php`

### Problem: Migrationen schlagen fehl
**Lösung:**
1. Datenbankverbindung testen:
```bash
php artisan migrate:status
```

2. Migrationen manuell ausführen:
```bash
php artisan migrate --path=database/migrations/2024_01_01_000001_create_queue_workers_table.php
```

## Support

- **GitHub Issues:** https://github.com/henningd/laravel-queue-manager/issues
- **Dokumentation:** https://github.com/henningd/laravel-queue-manager
- **Laravel Version:** 10.0+
- **PHP Version:** 8.1+

## Nächste Schritte

1. **Dashboard erkunden:** Besuchen Sie `/queue-manager`
2. **Worker erstellen:** Verwenden Sie die Web-UI oder CLI
3. **Queues konfigurieren:** Passen Sie Prioritäten und Limits an
4. **Monitoring einrichten:** Nutzen Sie Auto-Refresh und Alerts
5. **Security konfigurieren:** Beschränken Sie den Zugriff

Viel Erfolg mit Laravel Queue Manager! 🚀