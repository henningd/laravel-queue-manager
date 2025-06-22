# API Endpoints

Das Laravel Queue Manager Package bietet eine umfassende RESTful API für die Integration in externe Anwendungen, Mobile Apps oder Monitoring-Systeme.

## 🌐 API-Übersicht

### Base URL
```
http://your-app.com/api/queue-manager
```

### Authentifizierung
Die API verwendet Laravel's Standard-Authentifizierung. Je nach Konfiguration:
- **Session-basiert**: Für Web-Anwendungen
- **Token-basiert**: Für API-Clients
- **Sanctum**: Für SPA und Mobile Apps

### Content-Type
Alle Requests und Responses verwenden JSON:
```
Content-Type: application/json
Accept: application/json
```

## 📊 Statistik-Endpoints

### GET /stats
Ruft allgemeine Queue-Statistiken ab.

**Request:**
```bash
curl -X GET http://your-app.com/api/queue-manager/stats \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "active_jobs": 15,
    "pending_jobs": 42,
    "completed_jobs": 1247,
    "failed_jobs": 8,
    "total_workers": 5,
    "active_workers": 4,
    "queues": [
      {
        "name": "default",
        "pending": 25,
        "active": 3,
        "failed": 2
      },
      {
        "name": "emails",
        "pending": 17,
        "active": 2,
        "failed": 1
      }
    ],
    "performance": {
      "jobs_per_minute": 45.2,
      "average_job_time": 2.3,
      "success_rate": 99.4
    }
  },
  "timestamp": "2025-06-22T22:30:15Z"
}
```

### GET /stats/detailed
Detaillierte Statistiken mit historischen Daten.

**Query Parameters:**
- `period`: `hour`, `day`, `week`, `month` (default: `day`)
- `queue`: Spezifische Queue (optional)

**Request:**
```bash
curl -X GET "http://your-app.com/api/queue-manager/stats/detailed?period=hour&queue=emails" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "data": {
    "period": "hour",
    "queue": "emails",
    "metrics": {
      "total_jobs": 156,
      "completed_jobs": 152,
      "failed_jobs": 4,
      "average_duration": 1.8,
      "peak_throughput": 12.5,
      "error_rate": 2.6
    },
    "timeline": [
      {
        "timestamp": "2025-06-22T21:00:00Z",
        "jobs": 25,
        "duration": 1.5
      },
      {
        "timestamp": "2025-06-22T22:00:00Z",
        "jobs": 31,
        "duration": 2.1
      }
    ]
  }
}
```

## 👷 Worker-Endpoints

### GET /workers
Liste aller Workers abrufen.

**Request:**
```bash
curl -X GET http://your-app.com/api/queue-manager/workers \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "worker_001",
      "pid": 12345,
      "queue": "default",
      "status": "active",
      "started_at": "2025-06-22T10:30:15Z",
      "processed_jobs": 247,
      "memory_usage": 45.2,
      "cpu_usage": 12.5,
      "current_job": {
        "id": "job_789",
        "class": "App\\Jobs\\ProcessEmail",
        "started_at": "2025-06-22T22:29:45Z"
      }
    },
    {
      "id": "worker_002",
      "pid": 12346,
      "queue": "emails",
      "status": "idle",
      "started_at": "2025-06-22T10:30:20Z",
      "processed_jobs": 189,
      "memory_usage": 38.7,
      "cpu_usage": 5.2,
      "current_job": null
    }
  ]
}
```

### POST /workers
Neuen Worker erstellen.

**Request:**
```bash
curl -X POST http://your-app.com/api/queue-manager/workers \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "queue": "default",
    "timeout": 60,
    "memory": 512,
    "sleep": 3,
    "tries": 3
  }'
```

**Response:**
```json
{
  "success": true,
  "message": "Worker erfolgreich gestartet",
  "data": {
    "id": "worker_003",
    "pid": 12347,
    "queue": "default",
    "status": "starting",
    "config": {
      "timeout": 60,
      "memory": 512,
      "sleep": 3,
      "tries": 3
    }
  }
}
```

### GET /workers/{id}
Einzelnen Worker abrufen.

**Request:**
```bash
curl -X GET http://your-app.com/api/queue-manager/workers/worker_001 \
  -H "Accept: application/json"
```

### DELETE /workers/{id}
Worker stoppen und entfernen.

**Request:**
```bash
curl -X DELETE http://your-app.com/api/queue-manager/workers/worker_001 \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "message": "Worker erfolgreich gestoppt"
}
```

### POST /workers/restart
Alle Workers neu starten.

**Request:**
```bash
curl -X POST http://your-app.com/api/queue-manager/workers/restart \
  -H "Accept: application/json"
```

## 📋 Queue-Endpoints

### GET /queues
Liste aller Queues abrufen.

**Request:**
```bash
curl -X GET http://your-app.com/api/queue-manager/queues \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "name": "default",
      "connection": "database",
      "pending_jobs": 25,
      "active_jobs": 3,
      "failed_jobs": 2,
      "total_jobs": 1247,
      "workers": 2,
      "throughput": 15.7,
      "average_wait_time": 2.3,
      "status": "active"
    },
    {
      "name": "emails",
      "connection": "redis",
      "pending_jobs": 17,
      "active_jobs": 2,
      "failed_jobs": 1,
      "total_jobs": 892,
      "workers": 1,
      "throughput": 8.4,
      "average_wait_time": 1.8,
      "status": "active"
    }
  ]
}
```

### POST /queues
Neue Queue erstellen.

**Request:**
```bash
curl -X POST http://your-app.com/api/queue-manager/queues \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "reports",
    "connection": "database",
    "workers": 1,
    "priority": "low"
  }'
```

### GET /queues/{name}
Einzelne Queue abrufen.

**Request:**
```bash
curl -X GET http://your-app.com/api/queue-manager/queues/default \
  -H "Accept: application/json"
```

### DELETE /queues/{name}
Queue löschen (nur wenn leer).

**Request:**
```bash
curl -X DELETE http://your-app.com/api/queue-manager/queues/reports \
  -H "Accept: application/json"
```

### POST /queues/{name}/clear
Alle Jobs aus Queue entfernen.

**Request:**
```bash
curl -X POST http://your-app.com/api/queue-manager/queues/default/clear \
  -H "Accept: application/json"
```

### POST /queues/{name}/pause
Queue pausieren.

**Request:**
```bash
curl -X POST http://your-app.com/api/queue-manager/queues/default/pause \
  -H "Accept: application/json"
```

### POST /queues/{name}/resume
Queue fortsetzen.

**Request:**
```bash
curl -X POST http://your-app.com/api/queue-manager/queues/default/resume \
  -H "Accept: application/json"
```

## 🔧 Job-Endpoints

### GET /jobs
Liste aller Jobs abrufen.

**Query Parameters:**
- `status`: `pending`, `active`, `completed`, `failed`
- `queue`: Queue-Name
- `limit`: Anzahl Ergebnisse (default: 25, max: 100)
- `offset`: Offset für Pagination

**Request:**
```bash
curl -X GET "http://your-app.com/api/queue-manager/jobs?status=failed&limit=10" \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "job_123",
      "queue": "emails",
      "class": "App\\Jobs\\SendEmail",
      "status": "failed",
      "attempts": 3,
      "max_tries": 3,
      "created_at": "2025-06-22T20:15:30Z",
      "started_at": "2025-06-22T20:16:45Z",
      "failed_at": "2025-06-22T20:17:12Z",
      "exception": "Connection timeout",
      "payload": {
        "user_id": 42,
        "template": "welcome"
      }
    }
  ],
  "meta": {
    "total": 8,
    "limit": 10,
    "offset": 0,
    "has_more": false
  }
}
```

### GET /jobs/{id}
Einzelnen Job abrufen.

**Request:**
```bash
curl -X GET http://your-app.com/api/queue-manager/jobs/job_123 \
  -H "Accept: application/json"
```

### POST /jobs/{id}/retry
Fehlgeschlagenen Job erneut versuchen.

**Request:**
```bash
curl -X POST http://your-app.com/api/queue-manager/jobs/job_123/retry \
  -H "Accept: application/json"
```

### DELETE /jobs/{id}
Job löschen.

**Request:**
```bash
curl -X DELETE http://your-app.com/api/queue-manager/jobs/job_123 \
  -H "Accept: application/json"
```

### POST /jobs/retry-failed
Alle fehlgeschlagenen Jobs erneut versuchen.

**Request:**
```bash
curl -X POST http://your-app.com/api/queue-manager/jobs/retry-failed \
  -H "Accept: application/json"
```

### POST /jobs/clear-failed
Alle fehlgeschlagenen Jobs löschen.

**Request:**
```bash
curl -X POST http://your-app.com/api/queue-manager/jobs/clear-failed \
  -H "Accept: application/json"
```

## 🔍 Monitoring-Endpoints

### GET /health
System-Gesundheitsstatus.

**Request:**
```bash
curl -X GET http://your-app.com/api/queue-manager/health \
  -H "Accept: application/json"
```

**Response:**
```json
{
  "success": true,
  "status": "healthy",
  "checks": {
    "database": "ok",
    "redis": "ok",
    "workers": "ok",
    "queues": "ok"
  },
  "metrics": {
    "uptime": 86400,
    "memory_usage": 67.3,
    "cpu_usage": 23.1,
    "disk_usage": 45.8
  }
}
```

### GET /metrics
Prometheus-kompatible Metriken.

**Request:**
```bash
curl -X GET http://your-app.com/api/queue-manager/metrics \
  -H "Accept: text/plain"
```

**Response:**
```
# HELP queue_jobs_total Total number of jobs
# TYPE queue_jobs_total counter
queue_jobs_total{status="completed"} 1247
queue_jobs_total{status="failed"} 8
queue_jobs_total{status="pending"} 42

# HELP queue_workers_total Total number of workers
# TYPE queue_workers_total gauge
queue_workers_total{status="active"} 4
queue_workers_total{status="idle"} 1
```

## 🔐 Authentifizierung

### API Token
```bash
# Token in Header
curl -X GET http://your-app.com/api/queue-manager/stats \
  -H "Authorization: Bearer your-api-token" \
  -H "Accept: application/json"
```

### Session-basiert
```bash
# Mit Laravel Session
curl -X GET http://your-app.com/api/queue-manager/stats \
  -H "Cookie: laravel_session=your-session-cookie" \
  -H "Accept: application/json"
```

## 📝 Fehlerbehandlung

### Standard-Fehlerformat
```json
{
  "success": false,
  "error": {
    "code": "WORKER_NOT_FOUND",
    "message": "Worker mit ID 'worker_999' nicht gefunden",
    "details": {
      "worker_id": "worker_999",
      "available_workers": ["worker_001", "worker_002"]
    }
  },
  "timestamp": "2025-06-22T22:30:15Z"
}
```

### HTTP-Status-Codes
- `200`: Erfolg
- `201`: Erstellt
- `400`: Ungültige Anfrage
- `401`: Nicht authentifiziert
- `403`: Nicht autorisiert
- `404`: Nicht gefunden
- `422`: Validierungsfehler
- `500`: Server-Fehler

### Häufige Fehlercodes
- `WORKER_NOT_FOUND`: Worker existiert nicht
- `QUEUE_NOT_FOUND`: Queue existiert nicht
- `JOB_NOT_FOUND`: Job existiert nicht
- `INVALID_PARAMETERS`: Ungültige Parameter
- `WORKER_START_FAILED`: Worker konnte nicht gestartet werden
- `QUEUE_NOT_EMPTY`: Queue ist nicht leer
- `PERMISSION_DENIED`: Keine Berechtigung

## 📚 SDK und Libraries

### PHP SDK
```php
use HenningD\LaravelQueueManager\Client\QueueManagerClient;

$client = new QueueManagerClient('http://your-app.com/api/queue-manager');
$client->setToken('your-api-token');

// Statistiken abrufen
$stats = $client->getStats();

// Worker erstellen
$worker = $client->createWorker([
    'queue' => 'emails',
    'timeout' => 60
]);

// Jobs abrufen
$jobs = $client->getJobs(['status' => 'failed']);
```

### JavaScript SDK
```javascript
import QueueManagerClient from 'laravel-queue-manager-js';

const client = new QueueManagerClient({
    baseURL: 'http://your-app.com/api/queue-manager',
    token: 'your-api-token'
});

// Statistiken abrufen
const stats = await client.getStats();

// Worker erstellen
const worker = await client.createWorker({
    queue: 'emails',
    timeout: 60
});

// Real-time Updates
client.subscribe('stats', (data) => {
    console.log('Stats updated:', data);
});
```

## 🔄 Webhooks

### Webhook-Konfiguration
```php
// config/queue-manager.php
'webhooks' => [
    'enabled' => true,
    'endpoints' => [
        'http://your-app.com/webhooks/queue-manager'
    ],
    'events' => [
        'worker.started',
        'worker.stopped',
        'worker.failed',
        'job.completed',
        'job.failed',
        'queue.empty'
    ]
]
```

### Webhook-Payload
```json
{
  "event": "worker.failed",
  "timestamp": "2025-06-22T22:30:15Z",
  "data": {
    "worker_id": "worker_001",
    "queue": "emails",
    "error": "Memory limit exceeded",
    "context": {
      "memory_usage": 512,
      "processed_jobs": 247
    }
  }
}
```

## ➡️ Nächste Schritte

- [Konfigurationsoptionen](Konfigurationsoptionen.md) - API-Konfiguration anpassen
- [Anpassungen](Anpassungen.md) - Eigene Endpoints hinzufügen
- [Debugging](Debugging.md) - API-Probleme beheben