# Queue Management

Das Queue Management ist ein zentraler Bestandteil des Laravel Queue Manager Packages. Diese Anleitung zeigt dir, wie du Queues effektiv verwaltest, konfigurierst und optimierst.

## 🎯 Übersicht

Queue Management umfasst:

1. **Queue-Erstellung und -Konfiguration** - Neue Queues einrichten
2. **Queue-Überwachung** - Status und Performance überwachen
3. **Prioritäten-Management** - Queue-Prioritäten festlegen
4. **Rate-Limiting** - Durchsatz-Begrenzungen konfigurieren
5. **Queue-Wartung** - Bereinigung und Optimierung
6. **Erweiterte Konfiguration** - Spezielle Einstellungen

## 📋 Queue-Übersicht

### Queue-Dashboard aufrufen

Navigiere zum Queue-Bereich:
- **URL**: `/queue-manager/queues`
- **Navigation**: Sidebar → "📋 Queues"

### Queue-Tabelle verstehen

Die Queue-Übersicht zeigt:

| Spalte | Beschreibung | Beispiel |
|--------|--------------|----------|
| **Name** | Queue-Bezeichnung | `emails`, `notifications` |
| **Verbindung** | Queue-Driver | `database`, `redis` |
| **Wartende Jobs** | Jobs in Warteschlange | `15` |
| **Aktive Jobs** | Aktuell verarbeitete Jobs | `3` |
| **Fehlgeschlagen** | Anzahl fehlgeschlagener Jobs | `2` |
| **Durchsatz** | Jobs pro Minute | `45/min` |
| **Priorität** | Queue-Priorität (1-10) | `5` |
| **Status** | Aktiv/Pausiert/Gestoppt | `Aktiv` |
| **Aktionen** | Verfügbare Operationen | Bearbeiten, Löschen |

## ➕ Neue Queue erstellen

### Option A: Über das Dashboard

1. **Queue-Bereich öffnen**
   - Klicke auf "📋 Queues" in der Sidebar

2. **Neue Queue erstellen**
   - Klicke auf "➕ Neue Queue"
   - Fülle das Formular aus:

```
Name: high-priority-emails
Beschreibung: Hochpriorisierte E-Mail-Versendung
Verbindung: redis
Priorität: 8
Max. Jobs pro Minute: 100
Max. Workers: 5
Auto-Scaling: Aktiviert
```

3. **Erweiterte Einstellungen**
   - **Timeout**: `120` Sekunden
   - **Memory Limit**: `256` MB
   - **Sleep Zeit**: `1` Sekunde
   - **Max. Versuche**: `3`
   - **Backoff**: `30` Sekunden

### Option B: Über die Kommandozeile

```bash
# Queue-Konfiguration erstellen
php artisan tinker
>>> use HenningD\LaravelQueueManager\Models\QueueConfiguration;
>>> QueueConfiguration::create([
...     'name' => 'high-priority-emails',
...     'description' => 'Hochpriorisierte E-Mail-Versendung',
...     'connection' => 'redis',
...     'priority' => 8,
...     'max_jobs_per_minute' => 100,
...     'max_workers' => 5,
...     'auto_scale' => true,
...     'timeout' => 120,
...     'memory' => 256,
...     'sleep' => 1,
...     'tries' => 3,
...     'backoff' => 30,
...     'is_active' => true
... ]);
>>> exit
```

### Option C: Über die Konfigurationsdatei

Bearbeite `config/queue-manager.php`:

```php
'queue_configurations' => [
    'high-priority-emails' => [
        'description' => 'Hochpriorisierte E-Mail-Versendung',
        'connection' => 'redis',
        'priority' => 8,
        'max_jobs_per_minute' => 100,
        'max_workers' => 5,
        'auto_scale' => true,
        'timeout' => 120,
        'memory' => 256,
        'sleep' => 1,
        'tries' => 3,
        'backoff' => 30,
    ],
],
```

Dann die Konfiguration laden:

```bash
php artisan queue-manager:seed --queues-only
```

## ⚙️ Queue-Konfiguration

### Basis-Einstellungen

#### Queue-Name
- **Format**: Kleinbuchstaben, Bindestriche erlaubt
- **Beispiele**: `default`, `emails`, `high-priority`, `background-tasks`
- **Vermeiden**: Leerzeichen, Sonderzeichen

#### Verbindung (Connection)
- **database**: Für einfache Setups
- **redis**: Für bessere Performance
- **sqs**: Für AWS-Integration
- **sync**: Nur für Tests

#### Priorität (1-10)
- **1-3**: Niedrige Priorität (Berichte, Cleanup)
- **4-6**: Normale Priorität (Standard-Jobs)
- **7-8**: Hohe Priorität (E-Mails, Benachrichtigungen)
- **9-10**: Kritische Priorität (Zahlungen, Sicherheit)

### Erweiterte Einstellungen

#### Rate-Limiting

```php
'max_jobs_per_minute' => 60,        // Maximale Jobs pro Minute
'rate_limiting_enabled' => true,    // Rate-Limiting aktivieren
'burst_limit' => 10,                // Burst-Limit für Spitzenlasten
```

#### Auto-Scaling

```php
'auto_scale' => true,               // Auto-Scaling aktivieren
'min_workers' => 1,                 // Minimum Workers
'max_workers' => 10,                // Maximum Workers
'scale_up_threshold' => 20,         // Skalierung nach oben bei X Jobs
'scale_down_threshold' => 5,        // Skalierung nach unten bei X Jobs
'scale_cooldown' => 300,            // Wartezeit zwischen Skalierungen (Sekunden)
```

#### Performance-Tuning

```php
'timeout' => 60,                    // Job-Timeout in Sekunden
'memory' => 128,                    // Memory-Limit in MB
'sleep' => 3,                       // Sleep-Zeit zwischen Jobs
'tries' => 3,                       // Maximale Wiederholungsversuche
'backoff' => [30, 60, 120],        // Backoff-Zeiten in Sekunden
```

## 📊 Queue-Überwachung

### Echtzeit-Monitoring

#### Dashboard-Metriken
- **Wartende Jobs**: Anzahl Jobs in der Warteschlange
- **Aktive Jobs**: Aktuell verarbeitete Jobs
- **Durchsatz**: Jobs pro Minute/Stunde
- **Fehlerrate**: Prozentsatz fehlgeschlagener Jobs
- **Durchschnittliche Wartezeit**: Zeit bis zur Verarbeitung

#### Performance-Indikatoren

```bash
# Queue-Status über CLI abrufen
php artisan queue:monitor

# Detaillierte Queue-Informationen
php artisan tinker
>>> DB::table('jobs')->where('queue', 'emails')->count()
>>> DB::table('failed_jobs')->where('queue', 'emails')->count()
```

### Alerts und Benachrichtigungen

#### Automatische Alerts konfigurieren

```php
// config/queue-manager.php
'alerts' => [
    'enabled' => true,
    'channels' => ['mail', 'slack'],
    'thresholds' => [
        'queue_length' => 100,          // Alert bei > 100 wartenden Jobs
        'failure_rate' => 5,            // Alert bei > 5% Fehlerrate
        'processing_time' => 300,       // Alert bei > 5 Min Verarbeitungszeit
        'worker_down' => true,          // Alert bei Worker-Ausfall
    ],
    'recipients' => [
        'mail' => ['admin@example.com'],
        'slack' => ['#alerts'],
    ],
],
```

#### Custom Alert-Handler

```php
// app/Listeners/QueueAlertListener.php
class QueueAlertListener
{
    public function handle($event)
    {
        if ($event->queueLength > 100) {
            // Custom Alert-Logik
            Mail::to('admin@example.com')->send(new QueueAlert($event));
        }
    }
}
```

## 🔧 Queue-Operationen

### Queue pausieren/aktivieren

#### Über das Dashboard
1. Queue-Tabelle öffnen
2. Aktionen-Menü bei gewünschter Queue
3. "Pausieren" oder "Aktivieren" wählen

#### Über die Kommandozeile

```bash
# Queue pausieren
php artisan queue:pause emails

# Queue wieder aktivieren
php artisan queue:resume emails

# Alle Queues pausieren
php artisan queue:pause --all
```

#### Programmatisch

```php
use HenningD\LaravelQueueManager\Models\QueueConfiguration;

// Queue pausieren
$queue = QueueConfiguration::where('name', 'emails')->first();
$queue->pause();

// Queue aktivieren
$queue->resume();

// Status prüfen
if ($queue->isPaused()) {
    echo "Queue ist pausiert";
}
```

### Queue leeren

#### Alle Jobs aus Queue entfernen

```bash
# Über Laravel Artisan
php artisan queue:clear emails

# Über Database (bei database driver)
php artisan tinker
>>> DB::table('jobs')->where('queue', 'emails')->delete()
```

#### Nur fehlgeschlagene Jobs entfernen

```bash
# Fehlgeschlagene Jobs löschen
php artisan queue:flush

# Spezifische fehlgeschlagene Jobs
php artisan queue:forget 5  # Job-ID 5 löschen
```

### Queue-Statistiken zurücksetzen

```php
// Statistiken für Queue zurücksetzen
$queue = QueueConfiguration::where('name', 'emails')->first();
$queue->resetStatistics();

// Oder über Dashboard: Aktionen → "Statistiken zurücksetzen"
```

## 🚀 Performance-Optimierung

### Queue-Prioritäten optimieren

#### Prioritäten-Schema

```php
// Empfohlene Prioritäten-Verteilung
'queues' => [
    'critical' => ['priority' => 10, 'workers' => 5],    // Zahlungen, Sicherheit
    'high' => ['priority' => 8, 'workers' => 3],         // E-Mails, Push-Notifications
    'normal' => ['priority' => 5, 'workers' => 2],       // Standard-Jobs
    'low' => ['priority' => 2, 'workers' => 1],          // Berichte, Cleanup
    'background' => ['priority' => 1, 'workers' => 1],   // Wartungsaufgaben
],
```

#### Worker-Verteilung optimieren

```bash
# Workers nach Priorität starten
php artisan queue:work --queue=critical,high,normal,low --timeout=60
```

### Rate-Limiting strategisch einsetzen

#### API-Rate-Limits berücksichtigen

```php
// Für externe APIs
'email-queue' => [
    'max_jobs_per_minute' => 60,    // SendGrid Limit
    'burst_limit' => 10,
],

'sms-queue' => [
    'max_jobs_per_minute' => 100,   // Twilio Limit
    'burst_limit' => 20,
],
```

#### Datenbank-Performance schützen

```php
// Für datenbank-intensive Jobs
'data-processing' => [
    'max_jobs_per_minute' => 30,
    'max_workers' => 2,             // Begrenzte DB-Connections
],
```

### Memory-Management

#### Memory-Limits setzen

```php
'queues' => [
    'image-processing' => [
        'memory' => 512,            // Für Bildverarbeitung
        'timeout' => 300,
    ],
    'csv-import' => [
        'memory' => 256,            // Für Datenimport
        'timeout' => 600,
    ],
],
```

#### Memory-Leaks vermeiden

```bash
# Worker regelmäßig neustarten
php artisan queue:work --max-jobs=100 --max-time=3600
```

## 🔄 Queue-Wartung

### Regelmäßige Bereinigung

#### Alte Jobs bereinigen

```bash
# Erfolgreiche Jobs älter als 24h löschen
php artisan queue:prune-batches --hours=24

# Fehlgeschlagene Jobs älter als 7 Tage
php artisan queue:prune-batches --hours=168 --unfinished=7
```

#### Automatische Bereinigung einrichten

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Täglich alte Jobs bereinigen
    $schedule->command('queue:prune-batches --hours=24')->daily();
    
    // Wöchentlich fehlgeschlagene Jobs bereinigen
    $schedule->command('queue:prune-batches --hours=168 --unfinished=7')->weekly();
}
```

### Queue-Health-Checks

#### Automatische Gesundheitsprüfung

```php
// app/Console/Commands/QueueHealthCheck.php
class QueueHealthCheck extends Command
{
    public function handle()
    {
        $queues = QueueConfiguration::active()->get();
        
        foreach ($queues as $queue) {
            $pendingJobs = DB::table('jobs')
                ->where('queue', $queue->name)
                ->count();
                
            if ($pendingJobs > $queue->alert_threshold) {
                // Alert senden
                $this->sendAlert($queue, $pendingJobs);
            }
        }
    }
}
```

#### Monitoring-Script

```bash
#!/bin/bash
# queue-monitor.sh

# Queue-Längen prüfen
for queue in default emails notifications; do
    count=$(php artisan tinker --execute="echo DB::table('jobs')->where('queue', '$queue')->count();")
    echo "Queue $queue: $count jobs"
    
    if [ $count -gt 100 ]; then
        echo "WARNING: Queue $queue has $count pending jobs"
    fi
done
```

## 🚨 Troubleshooting

### Häufige Queue-Probleme

#### Jobs bleiben in der Queue hängen

**Ursachen:**
- Worker nicht gestartet
- Queue pausiert
- Memory-Limit erreicht
- Deadlock in Job-Code

**Lösungen:**
```bash
# Worker-Status prüfen
php artisan queue-manager:worker:list

# Queue-Status prüfen
php artisan queue:monitor

# Worker neustarten
php artisan queue:restart

# Hängende Jobs manuell verarbeiten
php artisan queue:work --once --verbose
```

#### Hohe Fehlerrate

**Ursachen:**
- Fehlerhafte Job-Implementierung
- Externe Service nicht verfügbar
- Datenbank-Probleme

**Lösungen:**
```bash
# Fehlgeschlagene Jobs analysieren
php artisan queue:failed

# Spezifischen Job erneut versuchen
php artisan queue:retry 5

# Alle fehlgeschlagenen Jobs erneut versuchen
php artisan queue:retry all

# Job-Details anzeigen
php artisan queue:failed --id=5
```

#### Performance-Probleme

**Ursachen:**
- Zu viele Jobs pro Worker
- Ineffiziente Job-Implementierung
- Datenbank-Bottlenecks

**Lösungen:**
```bash
# Worker-Performance überwachen
php artisan queue:work --verbose

# Memory-Usage überwachen
php artisan queue:work --memory=128

# Job-Batching verwenden
php artisan make:job ProcessLargeDataset --batch
```

### Queue-spezifische Debugging

#### Job-Tracing aktivieren

```php
// config/queue-manager.php
'debugging' => [
    'enabled' => true,
    'log_job_start' => true,
    'log_job_end' => true,
    'log_job_failed' => true,
    'detailed_errors' => true,
],
```

#### Custom Queue-Logger

```php
// app/Listeners/QueueJobLogger.php
class QueueJobLogger
{
    public function handle($event)
    {
        Log::info('Queue Job', [
            'job' => $event->job->resolveName(),
            'queue' => $event->job->getQueue(),
            'attempts' => $event->job->attempts(),
            'payload' => $event->job->payload(),
        ]);
    }
}
```

## 📈 Erweiterte Queue-Strategien

### Multi-Tenant Queues

```php
// Tenant-spezifische Queues
'tenant_queues' => [
    'tenant_1_emails' => ['priority' => 5, 'max_workers' => 2],
    'tenant_2_emails' => ['priority' => 5, 'max_workers' => 2],
    'shared_background' => ['priority' => 1, 'max_workers' => 1],
],
```

### Geo-verteilte Queues

```php
// Region-spezifische Queues
'regional_queues' => [
    'eu_west_emails' => ['connection' => 'redis_eu'],
    'us_east_emails' => ['connection' => 'redis_us'],
    'asia_emails' => ['connection' => 'redis_asia'],
],
```

### Event-driven Queue Management

```php
// Automatische Queue-Erstellung basierend auf Events
Event::listen('user.registered', function ($event) {
    QueueConfiguration::createIfNotExists([
        'name' => "user_{$event->user->id}_onboarding",
        'priority' => 7,
        'auto_delete_after' => '7 days',
    ]);
});
```

## ➡️ Nächste Schritte

Nach dem Queue Management solltest du:

- **[Job Monitoring](Job-Monitoring.md)** - Einzelne Jobs detailliert überwachen
- **[Worker Management](Worker-Management.md)** - Worker optimal konfigurieren
- **[API Endpoints](API-Endpoints.md)** - Programmatische Queue-Verwaltung
- **[Konfiguration](Konfiguration.md)** - Erweiterte Systemeinstellungen

## 📚 Weiterführende Ressourcen

- [Laravel Queue Dokumentation](https://laravel.com/docs/queues)
- [Redis Queue Performance](https://redis.io/docs/manual/patterns/distributed-locks/)
- [AWS SQS Best Practices](https://docs.aws.amazon.com/AWSSimpleQueueService/latest/SQSDeveloperGuide/sqs-best-practices.html)