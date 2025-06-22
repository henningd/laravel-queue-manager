# Installation

Diese Anleitung führt dich durch die Installation des Laravel Queue Manager Packages in deiner Laravel-Anwendung.

## 📋 Systemanforderungen

Bevor du beginnst, stelle sicher, dass dein System die folgenden Anforderungen erfüllt:

- **PHP**: 8.1 oder höher
- **Laravel**: 10.0, 11.0 oder 12.0
- **Composer**: Neueste Version empfohlen
- **Datenbank**: MySQL, PostgreSQL, SQLite oder SQL Server
- **Queue Driver**: Konfiguriert (database, redis, sqs, etc.)

## 🚀 Installationsmethoden

### Methode 1: Composer (Empfohlen)

```bash
composer require henningd/laravel-queue-manager
```

### Methode 2: Lokale Installation

Falls die Composer-Installation nicht funktioniert, kannst du das Package lokal installieren:

```bash
# Repository klonen
git clone https://github.com/henningd/laravel-queue-manager.git

# In dein Laravel-Projekt kopieren
cp -r laravel-queue-manager/src vendor/henningd/laravel-queue-manager/
cp -r laravel-queue-manager/resources vendor/henningd/laravel-queue-manager/
cp -r laravel-queue-manager/config vendor/henningd/laravel-queue-manager/
```

Dann in deiner `composer.json` hinzufügen:

```json
{
    "autoload": {
        "psr-4": {
            "HenningD\\LaravelQueueManager\\": "vendor/henningd/laravel-queue-manager/src/"
        }
    }
}
```

## ⚙️ Setup-Prozess

### Schritt 1: Automatische Installation

Führe den Installationsbefehl aus:

```bash
php artisan queue-manager:install
```

Dieser Befehl wird:
- Konfigurationsdateien publizieren
- Datenbank-Migrationen ausführen
- Views publizieren
- Beispieldaten erstellen (optional)
- Routen registrieren (optional)

### Schritt 2: Routen-Konfiguration

Du hast zwei Optionen für die Routen-Konfiguration:

#### Option A: Automatische ServiceProvider-Routen (Empfohlen)
Das Package registriert automatisch alle Routen über den ServiceProvider. Keine weitere Konfiguration erforderlich.

#### Option B: Manuelle Routen-Registrierung
Wenn du die Routen manuell verwalten möchtest, wähle diese Option während der Installation.

**Web-Routen** (in `routes/web.php`):
```php
// Laravel Queue Manager Routes
Route::prefix('queue-manager')->group(function () {
    Route::get('/', [QueueManagerController::class, 'dashboard'])->name('queue-manager.dashboard');
    Route::get('/workers', [QueueManagerController::class, 'workers'])->name('queue-manager.workers');
    Route::post('/workers', [QueueManagerController::class, 'createWorker'])->name('queue-manager.workers.create');
    Route::delete('/workers/{id}', [QueueManagerController::class, 'deleteWorker'])->name('queue-manager.workers.delete');
    Route::get('/queues', [QueueManagerController::class, 'queues'])->name('queue-manager.queues');
    Route::post('/queues', [QueueManagerController::class, 'createQueue'])->name('queue-manager.queues.create');
    Route::delete('/queues/{name}', [QueueManagerController::class, 'deleteQueue'])->name('queue-manager.queues.delete');
    Route::post('/restart-workers', [QueueManagerController::class, 'restartWorkers'])->name('queue-manager.restart-workers');
    Route::post('/retry-failed', [QueueManagerController::class, 'retryFailed'])->name('queue-manager.retry-failed');
});
```

**API-Routen** (in `routes/api.php`):
```php
// Laravel Queue Manager API Routes
Route::prefix('queue-manager')->group(function () {
    Route::get('/stats', [QueueManagerController::class, 'getStats']);
    Route::get('/workers', [QueueManagerController::class, 'getWorkers']);
    Route::get('/queues', [QueueManagerController::class, 'getQueues']);
    Route::get('/jobs', [QueueManagerController::class, 'getJobs']);
    Route::post('/workers', [QueueManagerController::class, 'createWorker']);
    Route::delete('/workers/{id}', [QueueManagerController::class, 'deleteWorker']);
    Route::post('/queues', [QueueManagerController::class, 'createQueue']);
    Route::delete('/queues/{name}', [QueueManagerController::class, 'deleteQueue']);
    Route::post('/restart-workers', [QueueManagerController::class, 'restartWorkers']);
    Route::post('/retry-failed', [QueueManagerController::class, 'retryFailed']);
});
```

### Schritt 3: Datenbank-Setup

Führe die Migrationen aus (falls nicht automatisch geschehen):

```bash
php artisan migrate
```

### Schritt 4: Queue-Konfiguration

Stelle sicher, dass deine Queue-Konfiguration in `config/queue.php` korrekt ist:

```php
'default' => env('QUEUE_CONNECTION', 'database'),

'connections' => [
    'database' => [
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
    ],
    // Weitere Verbindungen...
],
```

### Schritt 5: Beispieldaten (Optional)

Erstelle Beispieldaten für Tests:

```bash
php artisan queue-manager:seed
```

## 🔧 Konfiguration

### Umgebungsvariablen

Füge diese Variablen zu deiner `.env` Datei hinzu:

```env
# Queue-Konfiguration
QUEUE_CONNECTION=database

# Queue Manager Einstellungen
QUEUE_MANAGER_ENABLED=true
QUEUE_MANAGER_MIDDLEWARE=web
QUEUE_MANAGER_PREFIX=queue-manager
```

### Konfigurationsdatei

Die Hauptkonfiguration findest du in `config/queue-manager.php`:

```php
return [
    'enabled' => env('QUEUE_MANAGER_ENABLED', true),
    'middleware' => ['web'],
    'prefix' => 'queue-manager',
    'dashboard' => [
        'refresh_interval' => 5000, // Millisekunden
        'items_per_page' => 25,
    ],
];
```

## ✅ Installation überprüfen

### Schritt 1: Web-Interface testen

Besuche `http://your-app.com/queue-manager` in deinem Browser. Du solltest das Dashboard sehen.

### Schritt 2: API testen

Teste die API-Endpoints:

```bash
curl http://your-app.com/api/queue-manager/stats
```

### Schritt 3: Queue-Worker starten

Starte einen Queue-Worker:

```bash
php artisan queue:work
```

## 🚨 Häufige Installationsprobleme

### Problem: "Class not found"
**Lösung**: Führe `composer dump-autoload` aus.

### Problem: "Route not found"
**Lösung**: Cache leeren mit `php artisan route:clear`.

### Problem: "Migration failed"
**Lösung**: Überprüfe Datenbankverbindung und Berechtigungen.

### Problem: "Views not found"
**Lösung**: Führe `php artisan view:clear` und `php artisan queue-manager:install` erneut aus.

## 🔄 Update-Prozess

Um auf eine neue Version zu aktualisieren:

```bash
# Package aktualisieren
composer update henningd/laravel-queue-manager

# Neue Assets publizieren
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider" --force

# Migrationen ausführen
php artisan migrate
```

## 📞 Support

Bei Problemen:
1. Überprüfe die [Häufige Probleme](Häufige-Probleme.md) Sektion
2. Schaue in die [GitHub Issues](https://github.com/henningd/laravel-queue-manager/issues)
3. Erstelle ein neues Issue mit detaillierter Problembeschreibung

## ➡️ Nächste Schritte

Nach erfolgreicher Installation:
- [Konfiguration](Konfiguration.md) - Passe das Package an deine Bedürfnisse an
- [Dashboard Übersicht](Dashboard-Übersicht.md) - Lerne die Benutzeroberfläche kennen
- [Worker Management](Worker-Management.md) - Verwalte deine Queue-Workers