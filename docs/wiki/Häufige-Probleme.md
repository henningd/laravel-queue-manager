# Häufige Probleme

Diese Seite sammelt die häufigsten Probleme und deren Lösungen beim Einsatz des Laravel Queue Manager Packages.

## 🚨 Installation & Setup

### Problem: "Class 'HenningD\LaravelQueueManager\QueueManagerServiceProvider' not found"

**Ursache**: Package nicht korrekt installiert oder Autoloader nicht aktualisiert.

**Lösungen**:
```bash
# Autoloader neu generieren
composer dump-autoload

# Package neu installieren
composer require henningd/laravel-queue-manager

# Cache leeren
php artisan config:clear
php artisan cache:clear
```

### Problem: "Route [queue-manager.dashboard] not defined"

**Ursache**: Routen nicht registriert oder Cache-Problem.

**Lösungen**:
```bash
# Route-Cache leeren
php artisan route:clear

# Routen neu registrieren
php artisan queue-manager:install

# Konfiguration überprüfen
php artisan route:list | grep queue-manager
```

### Problem: "SQLSTATE[42S02]: Base table or field doesn't exist"

**Ursache**: Migrationen nicht ausgeführt.

**Lösungen**:
```bash
# Migrationen ausführen
php artisan migrate

# Spezifische Migration
php artisan migrate --path=/database/migrations/queue_manager

# Migration-Status prüfen
php artisan migrate:status
```

### Problem: "Views not found"

**Ursache**: Views nicht publiziert oder falsche Pfade.

**Lösungen**:
```bash
# Views publizieren
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider" --tag="views"

# View-Cache leeren
php artisan view:clear

# Alle Assets neu publizieren
php artisan queue-manager:install --force
```

## 🔧 Konfiguration

### Problem: Dashboard zeigt keine Daten

**Ursache**: Queue-Konfiguration oder Datenbankverbindung.

**Lösungen**:
```bash
# Queue-Konfiguration prüfen
php artisan config:show queue

# Datenbankverbindung testen
php artisan tinker
>>> DB::connection()->getPdo();

# Queue-Tabellen prüfen
php artisan queue:failed-table
php artisan migrate
```

### Problem: "Permission denied" beim Zugriff

**Ursache**: Middleware oder Authentifizierung.

**Lösungen**:
```php
// config/queue-manager.php
'middleware' => ['web'], // Statt ['auth']

// Oder eigene Middleware
'middleware' => ['web', 'custom-auth'],
```

### Problem: Assets (CSS/JS) laden nicht

**Ursache**: Asset-Pfade oder Webserver-Konfiguration.

**Lösungen**:
```bash
# Assets publizieren
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider" --tag="assets"

# Symlink erstellen
php artisan storage:link

# Webserver-Konfiguration prüfen (Apache/Nginx)
```

## 👷 Worker-Probleme

### Problem: Workers starten nicht

**Ursache**: Verschiedene mögliche Ursachen.

**Diagnose**:
```bash
# Worker manuell starten (Debug)
php artisan queue:work --verbose

# Logs überprüfen
tail -f storage/logs/laravel.log

# Prozesse prüfen
ps aux | grep "queue:work"
```

**Lösungen**:
```bash
# Queue-Konfiguration reparieren
php artisan config:cache

# Worker neu starten
php artisan queue:restart

# Supervisor neu starten (falls verwendet)
sudo supervisorctl restart laravel-worker:*
```

### Problem: Worker verbrauchen zu viel Speicher

**Ursache**: Memory Leaks oder große Jobs.

**Lösungen**:
```bash
# Memory Limit setzen
php artisan queue:work --memory=512

# Worker regelmäßig neu starten
php artisan queue:work --max-jobs=100 --max-time=3600

# Memory-optimierte Konfiguration
php artisan queue:work --sleep=3 --tries=3 --timeout=60
```

**Code-Optimierung**:
```php
// In deinen Jobs
public function handle()
{
    // Große Objekte nach Verwendung freigeben
    unset($largeObject);
    
    // Garbage Collection
    gc_collect_cycles();
    
    // Memory-effiziente Datenbankabfragen
    User::chunk(100, function ($users) {
        // Verarbeitung in Chunks
    });
}
```

### Problem: Workers hängen sich auf

**Ursache**: Deadlocks, Endlosschleifen oder externe Timeouts.

**Lösungen**:
```bash
# Timeout setzen
php artisan queue:work --timeout=60

# Hängende Prozesse finden und beenden
ps aux | grep "queue:work"
kill -9 [PID]

# Worker mit Überwachung starten
timeout 3600 php artisan queue:work
```

**Präventive Maßnahmen**:
```php
// In Jobs
public function handle()
{
    // Timeout für externe APIs
    $client = new GuzzleHttp\Client([
        'timeout' => 30,
        'connect_timeout' => 10
    ]);
    
    // Deadlock-Vermeidung
    DB::transaction(function () {
        // Kurze Transaktionen
    }, 3); // Max 3 Versuche
}
```

## 📋 Queue-Probleme

### Problem: Jobs bleiben in "pending" Status

**Ursache**: Keine aktiven Workers oder Queue-Konfiguration.

**Lösungen**:
```bash
# Workers starten
php artisan queue:work

# Queue-Status prüfen
php artisan queue:monitor

# Spezifische Queue verarbeiten
php artisan queue:work --queue=emails,default
```

### Problem: Jobs schlagen fehl ohne Fehlermeldung

**Ursache**: Exception-Handling oder Logging-Probleme.

**Lösungen**:
```bash
# Failed Jobs anzeigen
php artisan queue:failed

# Detaillierte Logs aktivieren
# In config/logging.php
'channels' => [
    'queue' => [
        'driver' => 'single',
        'path' => storage_path('logs/queue.log'),
        'level' => 'debug',
    ],
],
```

**Job-Debugging**:
```php
// In deinem Job
use Illuminate\Support\Facades\Log;

public function handle()
{
    try {
        Log::info('Job started', ['job_id' => $this->job->getJobId()]);
        
        // Deine Logik hier
        
        Log::info('Job completed successfully');
    } catch (\Exception $e) {
        Log::error('Job failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}

public function failed(\Exception $exception)
{
    Log::error('Job definitively failed', [
        'error' => $exception->getMessage(),
        'job_data' => $this->toArray()
    ]);
}
```

### Problem: Hohe Anzahl fehlgeschlagener Jobs

**Ursache**: Systemprobleme oder fehlerhafte Job-Implementierung.

**Analyse**:
```bash
# Failed Jobs analysieren
php artisan queue:failed

# Häufigste Fehler finden
php artisan tinker
>>> DB::table('failed_jobs')->select('exception')->get()->groupBy('exception')->map->count()->sortDesc()
```

**Lösungen**:
```bash
# Einzelne Jobs erneut versuchen
php artisan queue:retry [job-id]

# Alle failed Jobs erneut versuchen
php artisan queue:retry all

# Failed Jobs löschen
php artisan queue:flush
```

## 🌐 Dashboard & UI

### Problem: Dashboard lädt langsam

**Ursache**: Große Datenmengen oder ineffiziente Queries.

**Lösungen**:
```php
// config/queue-manager.php
'dashboard' => [
    'refresh_interval' => 10000, // Längeres Intervall
    'items_per_page' => 10,      // Weniger Items
    'enable_auto_refresh' => false, // Auto-refresh deaktivieren
],
```

**Datenbankoptimierung**:
```bash
# Indizes hinzufügen
php artisan make:migration add_indexes_to_jobs_table

# Alte Jobs archivieren
php artisan queue:prune-batches --hours=48
```

### Problem: JavaScript-Fehler im Dashboard

**Ursache**: Asset-Konflikte oder veraltete Browser.

**Lösungen**:
```bash
# Browser-Cache leeren
# Entwicklertools öffnen (F12)
# Console-Fehler prüfen

# Assets neu kompilieren
npm run dev
# oder
npm run production
```

### Problem: Mobile Ansicht funktioniert nicht

**Ursache**: CSS-Probleme oder fehlende Responsive-Styles.

**Lösungen**:
```bash
# Views neu publizieren
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider" --tag="views" --force

# CSS-Cache leeren
php artisan view:clear
```

## 🔌 API-Probleme

### Problem: API-Endpoints nicht erreichbar

**Ursache**: Routen nicht registriert oder Middleware-Probleme.

**Lösungen**:
```bash
# API-Routen prüfen
php artisan route:list | grep api/queue-manager

# API-Routen registrieren
php artisan queue-manager:install --api

# CORS-Probleme lösen
# In config/cors.php
'paths' => ['api/*', 'queue-manager/*'],
```

### Problem: "Unauthenticated" bei API-Calls

**Ursache**: Authentifizierung oder Token-Probleme.

**Lösungen**:
```php
// config/queue-manager.php
'api' => [
    'middleware' => ['api'], // Statt ['auth:api']
    'authentication' => false, // Für öffentliche APIs
],
```

**Token-basierte Auth**:
```bash
# Sanctum installieren
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

# Token erstellen
$user = User::find(1);
$token = $user->createToken('queue-manager')->plainTextToken;
```

## 🔍 Performance-Probleme

### Problem: Langsame Queue-Verarbeitung

**Ursache**: Ineffiziente Jobs oder Ressourcenmangel.

**Optimierungen**:
```bash
# Mehr Workers starten
php artisan queue:work --queue=high,default,low

# Redis als Queue-Driver verwenden
# In .env
QUEUE_CONNECTION=redis

# Supervisor für Worker-Management
sudo apt-get install supervisor
```

**Job-Optimierung**:
```php
// Effiziente Datenbankabfragen
public function handle()
{
    // Eager Loading verwenden
    $users = User::with('profile', 'orders')->get();
    
    // Bulk-Operationen
    User::whereIn('id', $userIds)->update(['status' => 'processed']);
    
    // Chunking für große Datenmengen
    User::chunk(1000, function ($users) {
        foreach ($users as $user) {
            // Verarbeitung
        }
    });
}
```

### Problem: Hoher Speicherverbrauch

**Ursache**: Memory Leaks oder ineffiziente Datenverarbeitung.

**Lösungen**:
```bash
# Memory Limit erhöhen (temporär)
php -d memory_limit=1G artisan queue:work

# Worker-Rotation aktivieren
php artisan queue:work --max-jobs=50 --max-time=1800
```

**Code-Optimierung**:
```php
public function handle()
{
    // Große Arrays vermeiden
    foreach ($this->items as $item) {
        $this->processItem($item);
        unset($item); // Speicher freigeben
    }
    
    // Datenbankverbindungen schließen
    DB::disconnect();
    
    // Garbage Collection
    if (memory_get_usage() > 100 * 1024 * 1024) { // 100MB
        gc_collect_cycles();
    }
}
```

## 🔧 Debugging-Tools

### Allgemeine Debug-Befehle
```bash
# System-Status prüfen
php artisan queue-manager:status

# Konfiguration anzeigen
php artisan config:show queue-manager

# Logs in Echtzeit verfolgen
tail -f storage/logs/laravel.log

# Queue-Statistiken
php artisan queue:monitor
```

### Debug-Modus aktivieren
```php
// config/queue-manager.php
'debug' => env('QUEUE_MANAGER_DEBUG', false),

// .env
QUEUE_MANAGER_DEBUG=true
LOG_LEVEL=debug
```

### Erweiterte Diagnose
```bash
# Systemressourcen prüfen
free -h
df -h
top

# Datenbankverbindung testen
php artisan tinker
>>> DB::connection()->getPdo()
>>> DB::table('jobs')->count()

# Queue-Konfiguration validieren
php artisan queue:work --once --verbose
```

## 📞 Support erhalten

### Bevor du Hilfe suchst
1. **Logs prüfen**: `storage/logs/laravel.log`
2. **Konfiguration validieren**: `php artisan config:show queue-manager`
3. **System-Status**: `php artisan queue-manager:status`
4. **Version prüfen**: `composer show henningd/laravel-queue-manager`

### Hilfe-Kanäle
1. **GitHub Issues**: [Repository Issues](https://github.com/henningd/laravel-queue-manager/issues)
2. **Dokumentation**: Diese Wiki-Seiten
3. **Laravel Community**: Discord, Reddit, Stack Overflow

### Issue-Report erstellen
Wenn du ein Issue erstellst, füge folgende Informationen hinzu:
- Laravel-Version
- PHP-Version
- Package-Version
- Fehlermeldung (vollständig)
- Schritte zur Reproduktion
- Relevante Konfiguration
- Log-Ausgaben

```bash
# System-Info sammeln
php --version
php artisan --version
composer show henningd/laravel-queue-manager
```

## ➡️ Nächste Schritte

- [Debugging](Debugging.md) - Erweiterte Debugging-Techniken
- [Performance Optimierung](Performance-Optimierung.md) - System-Performance verbessern
- [Konfigurationsoptionen](Konfigurationsoptionen.md) - Detaillierte Konfiguration