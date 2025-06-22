# Erste Einrichtung

Diese Anleitung führt dich durch die ersten Schritte nach der erfolgreichen Installation des Laravel Queue Manager Packages. Du lernst, wie du das System konfigurierst, deinen ersten Worker erstellst und die Funktionalität testest.

## 🎯 Übersicht

Nach der Installation wirst du in dieser Anleitung:

1. **Dashboard aufrufen** - Erste Orientierung in der Benutzeroberfläche
2. **Grundkonfiguration überprüfen** - Wichtige Einstellungen kontrollieren
3. **Ersten Worker erstellen** - Queue-Verarbeitung einrichten
4. **Test-Job ausführen** - Funktionalität überprüfen
5. **Monitoring aktivieren** - Überwachung einrichten
6. **Sicherheit konfigurieren** - Zugriff absichern

## 📋 Voraussetzungen

Bevor du beginnst, stelle sicher, dass:

- ✅ Das Package erfolgreich installiert wurde (siehe [Installation](Installation.md))
- ✅ `php artisan queue-manager:install` ausgeführt wurde
- ✅ Die Migrationen erfolgreich liefen
- ✅ Deine Laravel-Anwendung läuft
- ✅ Eine Queue-Verbindung konfiguriert ist

## 🚀 Schritt 1: Dashboard aufrufen

### Dashboard öffnen

Öffne deinen Browser und navigiere zu:

```
http://your-app.com/queue-manager
```

**Beispiele für verschiedene Umgebungen:**
- **Lokale Entwicklung**: `http://localhost:8000/queue-manager`
- **Laravel Valet**: `http://myapp.test/queue-manager`
- **Homestead**: `http://homestead.test/queue-manager`
- **Produktionsumgebung**: `https://myapp.com/queue-manager`

### Erste Orientierung

Du solltest jetzt das Dashboard sehen mit:

- **📊 Statistik-Karten** - Zeigen aktuelle Queue-Metriken (alle sollten 0 sein)
- **👷 Worker-Bereich** - Übersicht über aktive Workers (leer)
- **📋 Queue-Bereich** - Verfügbare Queues
- **🔄 Auto-Refresh** - Automatische Aktualisierung alle 5 Sekunden

> **💡 Tipp:** Falls das Dashboard nicht lädt, überprüfe die [Häufige Probleme](Häufige-Probleme.md#dashboard-lädt-nicht) Sektion.

### Navigation erkunden

Mache dich mit der Sidebar-Navigation vertraut:

- **🏠 Dashboard** - Hauptübersicht (aktuell geöffnet)
- **👷 Workers** - Worker-Verwaltung
- **📋 Queues** - Queue-Management
- **📊 Jobs** - Job-Monitoring
- **⚙️ Einstellungen** - Konfiguration

## ⚙️ Schritt 2: Grundkonfiguration überprüfen

### Queue-Verbindung prüfen

Überprüfe deine Queue-Konfiguration in der `.env` Datei:

```env
# Basis Queue-Konfiguration
QUEUE_CONNECTION=database

# Optional: Queue Manager spezifische Einstellungen
QUEUE_MANAGER_ENABLED=true
QUEUE_MANAGER_MIDDLEWARE=web
QUEUE_MANAGER_PREFIX=queue-manager
```

### Datenbank-Tabellen überprüfen

Stelle sicher, dass alle notwendigen Tabellen existieren:

```bash
# Laravel Queue-Tabellen prüfen
php artisan tinker
>>> Schema::hasTable('jobs')           // true
>>> Schema::hasTable('failed_jobs')    // true
>>> Schema::hasTable('queue_workers')  // true
>>> Schema::hasTable('queue_configurations') // true
```

### Standard-Konfiguration laden

Falls noch nicht geschehen, lade die Standard-Konfigurationen:

```bash
php artisan queue-manager:seed
```

**Dies erstellt:**
- Standard-Queue-Konfigurationen für `default`, `emails`, `notifications`
- Beispiel-Worker-Einstellungen
- Basis-Monitoring-Regeln

### Konfigurationsdatei überprüfen

Überprüfe die Hauptkonfiguration in `config/queue-manager.php`:

```php
return [
    'enabled' => true,
    'middleware' => ['web'],
    'prefix' => 'queue-manager',
    
    'dashboard' => [
        'refresh_interval' => 5000, // 5 Sekunden
        'items_per_page' => 25,
        'show_statistics' => true,
    ],
    
    'workers' => [
        'default_timeout' => 60,
        'default_memory' => 128,
        'default_sleep' => 3,
    ],
];
```

## 👷 Schritt 3: Ersten Worker erstellen

### Option A: Über das Dashboard (Empfohlen für Anfänger)

1. **Worker-Bereich öffnen**
   - Klicke in der Sidebar auf "👷 Workers"
   - Oder besuche direkt: `/queue-manager/workers`

2. **Neuen Worker erstellen**
   - Klicke auf "➕ Neuer Worker"
   - Fülle das Formular aus:
     - **Name**: `Mein erster Worker`
     - **Anzeigename**: `Standard Worker`
     - **Queue**: `default`
     - **Timeout**: `60` Sekunden
     - **Memory**: `128` MB
     - **Sleep**: `3` Sekunden

3. **Worker starten**
   - Aktiviere "Sofort starten"
   - Klicke auf "Worker erstellen"

### Option B: Über die Kommandozeile

```bash
# Worker erstellen und sofort starten
php artisan queue-manager:worker:create "Mein erster Worker" \
    --queue=default \
    --timeout=60 \
    --memory=128 \
    --sleep=3 \
    --start

# Worker-Status überprüfen
php artisan queue-manager:worker:list
```

### Worker-Status überprüfen

Nach der Erstellung solltest du sehen:

```bash
# Alle Worker auflisten
php artisan queue-manager:worker:list

# Ausgabe sollte etwa so aussehen:
+----+-------------------+----------+----------+---------+---------------------+
| ID | Name              | Queue    | Status   | PID     | Started At          |
+----+-------------------+----------+----------+---------+---------------------+
| 1  | Mein erster Worker| default  | running  | 12345   | 2024-01-01 10:00:00 |
+----+-------------------+----------+----------+---------+---------------------+
```

## 🧪 Schritt 4: Test-Job ausführen

### Test-Job erstellen

Erstelle einen einfachen Test-Job:

```bash
# Job-Klasse generieren
php artisan make:job TestQueueJob
```

Bearbeite `app/Jobs/TestQueueJob.php`:

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestQueueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $message = 'Test-Job erfolgreich ausgeführt!'
    ) {}

    public function handle(): void
    {
        Log::info('Queue Manager Test: ' . $this->message);
        
        // Simuliere etwas Arbeit
        sleep(2);
        
        Log::info('Queue Manager Test: Job abgeschlossen');
    }
}
```

### Test-Job dispatchen

```bash
# Über Tinker
php artisan tinker
>>> App\Jobs\TestQueueJob::dispatch('Hallo vom Queue Manager!');
>>> exit

# Oder über eine Route (optional)
# In routes/web.php:
Route::get('/test-queue', function () {
    App\Jobs\TestQueueJob::dispatch('Test von Route');
    return 'Test-Job wurde zur Queue hinzugefügt!';
});
```

### Ergebnis überprüfen

1. **Dashboard beobachten**
   - Gehe zurück zum Dashboard
   - Du solltest sehen, wie die Statistiken sich ändern
   - "Aktive Jobs" sollte kurz 1 zeigen, dann wieder 0
   - "Erfolgreiche Jobs" sollte sich um 1 erhöhen

2. **Logs überprüfen**
   ```bash
   # Laravel Logs anschauen
   tail -f storage/logs/laravel.log
   
   # Du solltest sehen:
   # [2024-01-01 10:05:00] local.INFO: Queue Manager Test: Hallo vom Queue Manager!
   # [2024-01-01 10:05:02] local.INFO: Queue Manager Test: Job abgeschlossen
   ```

3. **Worker-Aktivität überprüfen**
   ```bash
   # Worker-Details anzeigen
   php artisan queue-manager:worker:list --detailed
   ```

## 📊 Schritt 5: Monitoring aktivieren

### Auto-Refresh konfigurieren

Das Dashboard aktualisiert sich automatisch alle 5 Sekunden. Du kannst dies anpassen:

```php
// config/queue-manager.php
'dashboard' => [
    'refresh_interval' => 3000, // 3 Sekunden
    'enable_auto_refresh' => true,
],
```

### Erweiterte Statistiken aktivieren

```php
// config/queue-manager.php
'dashboard' => [
    'show_statistics' => true,
    'show_worker_details' => true,
    'show_queue_details' => true,
    'show_job_history' => true,
],
```

### E-Mail-Benachrichtigungen (Optional)

```php
// config/queue-manager.php
'notifications' => [
    'enabled' => true,
    'channels' => ['mail'],
    'events' => [
        'worker_failed',
        'queue_stuck',
        'high_failure_rate',
    ],
    'recipients' => ['admin@example.com'],
],
```

## 🔒 Schritt 6: Sicherheit konfigurieren

### Zugriff beschränken

#### Option A: Middleware hinzufügen

```php
// config/queue-manager.php
'middleware' => ['web', 'auth'],
```

#### Option B: IP-Beschränkung

```php
// config/queue-manager.php
'security' => [
    'enabled' => true,
    'allowed_ips' => [
        '127.0.0.1',
        '192.168.1.0/24',
        '10.0.0.0/8',
    ],
],
```

#### Option C: Custom Middleware

Erstelle eine eigene Middleware:

```bash
php artisan make:middleware QueueManagerAccess
```

```php
// app/Http/Middleware/QueueManagerAccess.php
public function handle($request, Closure $next)
{
    if (!auth()->check() || !auth()->user()->isAdmin()) {
        abort(403, 'Zugriff verweigert');
    }
    
    return $next($request);
}
```

Registriere die Middleware:

```php
// config/queue-manager.php
'middleware' => ['web', 'auth', App\Http\Middleware\QueueManagerAccess::class],
```

### Produktionsumgebung

Für die Produktion empfohlene Einstellungen:

```php
// config/queue-manager.php
return [
    'enabled' => env('QUEUE_MANAGER_ENABLED', false),
    'middleware' => ['web', 'auth', 'admin'],
    
    'security' => [
        'enabled' => true,
        'allowed_ips' => ['your-admin-ip'],
        'require_auth' => true,
    ],
    
    'dashboard' => [
        'refresh_interval' => 10000, // 10 Sekunden
        'items_per_page' => 50,
    ],
];
```

```env
# .env für Produktion
QUEUE_MANAGER_ENABLED=true
QUEUE_MANAGER_MIDDLEWARE="web,auth,admin"
```

## ✅ Schritt 7: Installation überprüfen

### Checkliste

Gehe diese Checkliste durch, um sicherzustellen, dass alles korrekt funktioniert:

- [ ] **Dashboard erreichbar** - `/queue-manager` lädt ohne Fehler
- [ ] **Worker läuft** - Mindestens ein Worker ist aktiv
- [ ] **Test-Job funktioniert** - Job wurde erfolgreich verarbeitet
- [ ] **Statistiken aktualisieren sich** - Auto-Refresh funktioniert
- [ ] **Navigation funktioniert** - Alle Bereiche sind erreichbar
- [ ] **Sicherheit konfiguriert** - Zugriff ist angemessen beschränkt

### Funktionstest

Führe einen umfassenden Funktionstest durch:

```bash
# 1. Mehrere Test-Jobs dispatchen
php artisan tinker
>>> for ($i = 1; $i <= 5; $i++) {
...     App\Jobs\TestQueueJob::dispatch("Test Job #$i");
... }
>>> exit

# 2. Worker-Performance überwachen
php artisan queue-manager:worker:list --watch

# 3. Queue-Status überprüfen
php artisan queue:work --once --verbose
```

### Performance-Test

```bash
# Viele Jobs auf einmal dispatchen
php artisan tinker
>>> for ($i = 1; $i <= 100; $i++) {
...     App\Jobs\TestQueueJob::dispatch("Performance Test #$i");
... }
>>> exit

# Dashboard beobachten und Performance messen
```

## 🚨 Häufige Probleme bei der Einrichtung

### Problem: Worker startet nicht

**Symptome:**
- Worker wird als "stopped" angezeigt
- Keine Jobs werden verarbeitet

**Lösungen:**
```bash
# PHP-Funktionen überprüfen
php -r "echo function_exists('proc_open') ? 'OK' : 'FEHLT';"

# Berechtigungen überprüfen
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/

# Worker manuell starten
php artisan queue:work --queue=default --verbose
```

### Problem: Dashboard zeigt falsche Daten

**Symptome:**
- Statistiken sind immer 0
- Worker werden nicht angezeigt

**Lösungen:**
```bash
# Cache leeren
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Datenbank überprüfen
php artisan tinker
>>> DB::table('jobs')->count()
>>> DB::table('queue_workers')->count()
```

### Problem: Jobs werden nicht verarbeitet

**Symptome:**
- Jobs bleiben in der Queue
- "Wartende Jobs" Zähler steigt

**Lösungen:**
```bash
# Queue-Konfiguration überprüfen
php artisan queue:work --once --verbose

# Worker neustarten
php artisan queue:restart

# Queue-Tabelle überprüfen
php artisan tinker
>>> DB::table('jobs')->get()
```

## 🎉 Herzlichen Glückwunsch!

Du hast den Laravel Queue Manager erfolgreich eingerichtet! 

### Nächste Schritte

Jetzt kannst du:

1. **[Dashboard erkunden](Dashboard-Übersicht.md)** - Lerne alle Features kennen
2. **[Worker verwalten](Worker-Management.md)** - Erweiterte Worker-Konfiguration
3. **[Queues konfigurieren](Konfiguration.md)** - Detaillierte Einstellungen
4. **[API nutzen](API-Endpoints.md)** - Programmatische Integration
5. **[Probleme lösen](Häufige-Probleme.md)** - Troubleshooting-Guide

### Produktive Nutzung

Für den produktiven Einsatz solltest du:

- **Monitoring einrichten** - Überwache Worker-Performance
- **Backup-Strategien** - Sichere Queue-Konfigurationen
- **Skalierung planen** - Auto-Scaling für hohe Lasten
- **Sicherheit härten** - Produktionsgerechte Zugriffskontrollen

Viel Erfolg mit dem Laravel Queue Manager! 🚀