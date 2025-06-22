# Worker Management

Workers sind die Herzstücke deines Queue-Systems. Sie verarbeiten Jobs aus den Warteschlangen und sorgen für die Ausführung deiner Hintergrundaufgaben. Diese Anleitung zeigt dir, wie du Workers effektiv verwaltest.

## 🔧 Worker-Grundlagen

### Was sind Workers?
Workers sind Prozesse, die kontinuierlich Jobs aus Queues abrufen und verarbeiten. Sie laufen im Hintergrund und können:
- Jobs aus spezifischen Queues verarbeiten
- Mehrere Jobs parallel abarbeiten
- Bei Fehlern automatisch neu starten
- Ressourcenverbrauch überwachen

### Worker-Typen
- **Standard Workers**: Verarbeiten alle Job-Typen
- **Spezialisierte Workers**: Nur für bestimmte Job-Klassen
- **Priority Workers**: Für hochpriorisierte Jobs
- **Batch Workers**: Für Stapelverarbeitung

## 📊 Worker-Übersicht

### Worker-Dashboard
Das Worker-Dashboard zeigt alle aktiven Workers mit folgenden Informationen:

| Spalte | Beschreibung | Beispiel |
|--------|--------------|----------|
| **ID** | Eindeutige Worker-Identifikation | `worker_001` |
| **Queue** | Zugewiesene Queue | `default`, `emails`, `reports` |
| **Status** | Aktueller Zustand | Aktiv, Pausiert, Gestoppt |
| **PID** | Prozess-ID | `12345` |
| **Gestartet** | Startzeit | `2025-06-22 10:30:15` |
| **Verarbeitete Jobs** | Anzahl abgeschlossener Jobs | `1,247` |
| **Speicher** | RAM-Verbrauch | `45 MB` |
| **CPU** | Prozessorauslastung | `12%` |

### Status-Indikatoren
- 🟢 **Aktiv**: Worker verarbeitet Jobs
- 🟡 **Wartend**: Worker wartet auf Jobs
- 🔴 **Fehler**: Worker hat einen Fehler
- ⏸️ **Pausiert**: Worker temporär gestoppt
- ⏹️ **Gestoppt**: Worker beendet

## ➕ Worker erstellen

### Über das Dashboard
1. Klicke auf **"Neuen Worker starten"**
2. Wähle die gewünschte Queue
3. Konfiguriere Optionen:
   - **Timeout**: Maximale Ausführungszeit pro Job
   - **Memory Limit**: Speicherlimit für den Worker
   - **Sleep**: Wartezeit zwischen Jobs
   - **Tries**: Anzahl Wiederholungsversuche

### Über die Kommandozeile
```bash
# Standard Worker starten
php artisan queue:work

# Worker für spezifische Queue
php artisan queue:work --queue=emails

# Worker mit Optionen
php artisan queue:work --queue=default --timeout=60 --memory=512 --sleep=3 --tries=3
```

### Über die API
```bash
curl -X POST http://your-app.com/api/queue-manager/workers \
  -H "Content-Type: application/json" \
  -d '{
    "queue": "default",
    "timeout": 60,
    "memory": 512,
    "sleep": 3,
    "tries": 3
  }'
```

## ⚙️ Worker-Konfiguration

### Grundeinstellungen
```php
// config/queue.php
'workers' => [
    'default' => [
        'timeout' => 60,        // Sekunden
        'memory' => 512,        // MB
        'sleep' => 3,           // Sekunden
        'tries' => 3,           // Versuche
        'max_jobs' => 1000,     // Jobs vor Neustart
        'max_time' => 3600,     // Sekunden vor Neustart
    ],
],
```

### Erweiterte Optionen
```php
'workers' => [
    'emails' => [
        'queue' => 'emails',
        'timeout' => 30,
        'memory' => 256,
        'sleep' => 1,
        'tries' => 5,
        'backoff' => [1, 5, 10], // Exponential backoff
        'max_exceptions' => 3,
    ],
    'reports' => [
        'queue' => 'reports',
        'timeout' => 300,
        'memory' => 1024,
        'sleep' => 5,
        'tries' => 1,
        'force' => true,
    ],
],
```

## 🔄 Worker-Aktionen

### Worker starten
```bash
# Einzelner Worker
php artisan queue:work

# Mehrere Workers (Supervisor empfohlen)
for i in {1..5}; do
    php artisan queue:work --daemon &
done
```

### Worker stoppen
```bash
# Graceful Stop (aktuelle Jobs beenden)
php artisan queue:restart

# Sofortiger Stop
kill -TERM [PID]

# Über Dashboard
# Klicke auf "Stoppen" Button beim entsprechenden Worker
```

### Worker neu starten
```bash
# Alle Workers neu starten
php artisan queue:restart

# Spezifischen Worker neu starten
kill -USR2 [PID]
```

### Worker pausieren
```bash
# Worker pausieren (SIGTSTP)
kill -TSTP [PID]

# Worker fortsetzen (SIGCONT)
kill -CONT [PID]
```

## 📈 Worker-Monitoring

### Echtzeit-Überwachung
Das Dashboard zeigt live:
- **Aktuelle Jobs**: Was gerade verarbeitet wird
- **Performance-Metriken**: Durchsatz, Latenz, Fehlerrate
- **Ressourcenverbrauch**: CPU, Speicher, Netzwerk
- **Gesundheitsstatus**: Verfügbarkeit und Stabilität

### Metriken
```php
// Worker-Statistiken abrufen
$stats = app('queue-manager')->getWorkerStats();

// Beispiel-Ausgabe:
[
    'total_workers' => 5,
    'active_workers' => 4,
    'idle_workers' => 1,
    'failed_workers' => 0,
    'total_jobs_processed' => 12847,
    'jobs_per_minute' => 45.2,
    'average_job_time' => 2.3, // Sekunden
    'memory_usage' => 234.5,   // MB
    'cpu_usage' => 15.7,       // Prozent
]
```

### Alerts und Benachrichtigungen
Automatische Warnungen bei:
- Worker-Ausfällen
- Hohem Speicherverbrauch (> 80%)
- Langen Verarbeitungszeiten
- Häufigen Fehlern

## 🚨 Fehlerbehebung

### Häufige Worker-Probleme

#### Worker startet nicht
**Ursachen**:
- Datenbankverbindung fehlt
- Queue-Konfiguration falsch
- Berechtigungsprobleme

**Lösungen**:
```bash
# Konfiguration prüfen
php artisan config:cache
php artisan queue:failed-table
php artisan migrate

# Logs überprüfen
tail -f storage/logs/laravel.log

# Worker im Debug-Modus starten
php artisan queue:work --verbose
```

#### Worker verbraucht zu viel Speicher
**Ursachen**:
- Memory Leaks in Jobs
- Große Datenmengen
- Fehlende Garbage Collection

**Lösungen**:
```bash
# Memory Limit setzen
php artisan queue:work --memory=512

# Worker regelmäßig neu starten
php artisan queue:work --max-jobs=100

# Job-Code optimieren
// In deinem Job:
public function handle()
{
    // Speicher nach Job freigeben
    gc_collect_cycles();
}
```

#### Worker hängt sich auf
**Ursachen**:
- Deadlocks
- Endlosschleifen
- Externe API-Timeouts

**Lösungen**:
```bash
# Timeout setzen
php artisan queue:work --timeout=60

# Worker überwachen
ps aux | grep "queue:work"

# Hängende Prozesse beenden
pkill -f "queue:work"
```

### Debug-Techniken
```bash
# Verbose Logging
php artisan queue:work --verbose

# Einzelnen Job verarbeiten
php artisan queue:work --once

# Failed Jobs anzeigen
php artisan queue:failed

# Job erneut versuchen
php artisan queue:retry [job-id]
```

## 🔧 Supervisor-Integration

### Supervisor-Konfiguration
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/app/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=8
redirect_stderr=true
stdout_logfile=/path/to/your/app/storage/logs/worker.log
stopwaitsecs=3600
```

### Supervisor-Befehle
```bash
# Konfiguration neu laden
sudo supervisorctl reread
sudo supervisorctl update

# Workers starten
sudo supervisorctl start laravel-worker:*

# Workers stoppen
sudo supervisorctl stop laravel-worker:*

# Status prüfen
sudo supervisorctl status
```

## 📊 Performance-Optimierung

### Worker-Tuning
```php
// Optimierte Worker-Konfiguration
'workers' => [
    'high_priority' => [
        'queue' => 'high',
        'processes' => 2,
        'timeout' => 30,
        'memory' => 256,
        'sleep' => 1,
    ],
    'normal_priority' => [
        'queue' => 'default',
        'processes' => 4,
        'timeout' => 60,
        'memory' => 512,
        'sleep' => 3,
    ],
    'low_priority' => [
        'queue' => 'low',
        'processes' => 1,
        'timeout' => 300,
        'memory' => 1024,
        'sleep' => 10,
    ],
],
```

### Skalierung
- **Horizontal**: Mehr Worker-Prozesse
- **Vertikal**: Mehr Ressourcen pro Worker
- **Queue-Splitting**: Verschiedene Queues für verschiedene Job-Typen
- **Load Balancing**: Worker auf mehrere Server verteilen

## 🔐 Sicherheit

### Worker-Isolation
```bash
# Worker als separater User
sudo -u queue-user php artisan queue:work

# Chroot-Umgebung
chroot /var/www/app php artisan queue:work

# Docker-Container
docker run -d laravel-app php artisan queue:work
```

### Berechtigungen
```bash
# Minimale Berechtigungen für Worker
chmod 750 /path/to/worker/script
chown queue-user:queue-group /path/to/worker/script
```

## ➡️ Nächste Schritte

- [Queue Management](Queue-Management.md) - Queues konfigurieren und verwalten
- [Job Monitoring](Job-Monitoring.md) - Einzelne Jobs überwachen
- [Performance Optimierung](Performance-Optimierung.md) - System-Performance verbessern