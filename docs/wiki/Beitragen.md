# Beitragen

Willkommen bei der Laravel Queue Manager Community! Wir freuen uns über jeden Beitrag zur Verbesserung des Packages. Diese Anleitung zeigt dir, wie du effektiv zum Projekt beitragen kannst.

## 🎯 Übersicht

Beitragsmöglichkeiten umfassen:

1. **Bug-Reports** - Fehler melden und dokumentieren
2. **Feature-Requests** - Neue Funktionen vorschlagen
3. **Code-Beiträge** - Pull Requests erstellen
4. **Dokumentation** - Wiki und Code-Dokumentation verbessern
5. **Testing** - Tests schreiben und ausführen
6. **Community-Support** - Anderen Benutzern helfen
7. **Übersetzungen** - Mehrsprachige Unterstützung
8. **Performance-Optimierung** - Verbesserungen vorschlagen

## 🐛 Bug-Reports

### Bevor du einen Bug meldest

1. **Suche nach existierenden Issues** - Prüfe ob der Bug bereits gemeldet wurde
2. **Reproduziere den Fehler** - Stelle sicher, dass der Bug konsistent auftritt
3. **Sammle Informationen** - Logs, Screenshots, Systemdaten
4. **Teste mit aktueller Version** - Verwende die neueste Version

### Bug-Report erstellen

#### Template für Bug-Reports

```markdown
## Bug-Beschreibung
Eine klare und präzise Beschreibung des Bugs.

## Schritte zur Reproduktion
1. Gehe zu '...'
2. Klicke auf '...'
3. Scrolle nach unten zu '...'
4. Siehe Fehler

## Erwartetes Verhalten
Eine klare Beschreibung dessen, was du erwartet hast.

## Tatsächliches Verhalten
Was ist stattdessen passiert?

## Screenshots
Falls zutreffend, füge Screenshots hinzu.

## Umgebung
- **OS**: [z.B. Ubuntu 20.04, Windows 10]
- **PHP Version**: [z.B. 8.2.0]
- **Laravel Version**: [z.B. 10.0]
- **Package Version**: [z.B. 1.2.3]
- **Queue Driver**: [z.B. redis, database]

## Logs
```
Relevante Log-Ausgaben hier einfügen
```

## Zusätzlicher Kontext
Weitere Informationen zum Problem.
```

#### Beispiel Bug-Report

```markdown
## Bug-Beschreibung
Worker werden nicht korrekt gestoppt, wenn das Dashboard geschlossen wird.

## Schritte zur Reproduktion
1. Starte 3 Worker über das Dashboard
2. Schließe den Browser-Tab
3. Prüfe laufende Prozesse mit `ps aux | grep queue:work`
4. Worker laufen weiter, obwohl sie gestoppt werden sollten

## Erwartetes Verhalten
Worker sollten automatisch gestoppt werden, wenn die Dashboard-Session beendet wird.

## Tatsächliches Verhalten
Worker laufen weiter und müssen manuell gestoppt werden.

## Umgebung
- **OS**: Ubuntu 22.04
- **PHP Version**: 8.2.0
- **Laravel Version**: 10.48.0
- **Package Version**: 1.0.0
- **Queue Driver**: redis

## Logs
```
[2024-01-01 10:00:00] local.INFO: Worker started {"worker_id": 123}
[2024-01-01 10:05:00] local.WARNING: Dashboard session ended {"session_id": "abc123"}
[2024-01-01 10:05:00] local.ERROR: Failed to stop worker {"worker_id": 123, "error": "Process not found"}
```
```

## 💡 Feature-Requests

### Feature-Request-Richtlinien

1. **Beschreibe den Use-Case** - Warum wird das Feature benötigt?
2. **Erkläre den Nutzen** - Wie hilft es der Community?
3. **Berücksichtige Alternativen** - Gibt es andere Lösungsansätze?
4. **Denke an Kompatibilität** - Passt es zur bestehenden Architektur?

#### Template für Feature-Requests

```markdown
## Feature-Beschreibung
Eine klare Beschreibung des gewünschten Features.

## Problem/Use-Case
Welches Problem löst dieses Feature? Beschreibe den Anwendungsfall.

## Vorgeschlagene Lösung
Beschreibe deine Idee für die Implementierung.

## Alternativen
Welche anderen Lösungsansätze hast du in Betracht gezogen?

## Zusätzlicher Kontext
Screenshots, Mockups oder weitere Informationen.

## Implementierungs-Ideen
Falls du Ideen zur technischen Umsetzung hast.
```

#### Beispiel Feature-Request

```markdown
## Feature-Beschreibung
Automatische Worker-Skalierung basierend auf Queue-Länge und CPU-Auslastung.

## Problem/Use-Case
Bei schwankender Last müssen Worker manuell skaliert werden. Dies führt zu:
- Überlastung bei Spitzenzeiten
- Ressourcenverschwendung bei geringer Last
- Manuelle Überwachung erforderlich

## Vorgeschlagene Lösung
Implementierung eines Auto-Scaling-Systems mit:
- Konfigurierbaren Schwellenwerten
- Verschiedenen Skalierungs-Strategien
- Metriken-basierter Entscheidungsfindung
- Graceful Scale-Down

## Alternativen
- Externe Tools wie Kubernetes HPA
- Cron-basierte Skalierung
- Manuelle Skalierung beibehalten

## Implementierungs-Ideen
```php
// config/queue-manager.php
'auto_scaling' => [
    'enabled' => true,
    'strategies' => ['queue_length', 'cpu_usage'],
    'scale_up_threshold' => 20,
    'scale_down_threshold' => 5,
]
```
```

## 🔧 Code-Beiträge

### Entwicklungsumgebung einrichten

#### Repository forken und klonen

```bash
# 1. Fork das Repository auf GitHub
# 2. Klone dein Fork
git clone https://github.com/DEIN-USERNAME/laravel-queue-manager.git
cd laravel-queue-manager

# 3. Upstream-Remote hinzufügen
git remote add upstream https://github.com/henningd/laravel-queue-manager.git

# 4. Dependencies installieren
composer install
npm install
```

#### Lokale Entwicklungsumgebung

```bash
# Test-Laravel-App erstellen
composer create-project laravel/laravel test-app
cd test-app

# Package lokal verlinken
composer config repositories.local path ../laravel-queue-manager
composer require henningd/laravel-queue-manager:@dev

# Package installieren
php artisan queue-manager:install
```

#### Entwicklungs-Tools

```bash
# Code-Style prüfen
composer run-script cs-check

# Code-Style automatisch korrigieren
composer run-script cs-fix

# Tests ausführen
composer run-script test

# Static Analysis
composer run-script analyse
```

### Coding-Standards

#### PSR-12 Coding-Standard

```php
<?php

namespace HenningD\LaravelQueueManager\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service für Queue-Management-Operationen.
 */
class QueueService
{
    /**
     * Erstellt eine neue Queue-Konfiguration.
     *
     * @param array $config Queue-Konfiguration
     * @return QueueConfiguration
     * @throws \InvalidArgumentException
     */
    public function createQueue(array $config): QueueConfiguration
    {
        $this->validateConfig($config);
        
        return QueueConfiguration::create([
            'name' => $config['name'],
            'priority' => $config['priority'] ?? 5,
            'max_workers' => $config['max_workers'] ?? 10,
        ]);
    }
    
    /**
     * Validiert Queue-Konfiguration.
     *
     * @param array $config
     * @throws \InvalidArgumentException
     */
    private function validateConfig(array $config): void
    {
        if (empty($config['name'])) {
            throw new \InvalidArgumentException('Queue name is required');
        }
        
        if (isset($config['priority']) && ($config['priority'] < 1 || $config['priority'] > 10)) {
            throw new \InvalidArgumentException('Priority must be between 1 and 10');
        }
    }
}
```

#### Dokumentations-Standards

```php
/**
 * Startet einen neuen Worker für die angegebene Queue.
 *
 * @param string $queueName Name der Queue
 * @param array $options Worker-Optionen
 * @return WorkerProcess Der gestartete Worker-Prozess
 * 
 * @throws WorkerException Wenn der Worker nicht gestartet werden kann
 * @throws InvalidArgumentException Wenn ungültige Optionen übergeben werden
 * 
 * @example
 * ```php
 * $worker = $service->startWorker('emails', [
 *     'timeout' => 60,
 *     'memory' => 128,
 *     'sleep' => 3
 * ]);
 * ```
 */
public function startWorker(string $queueName, array $options = []): WorkerProcess
{
    // Implementation...
}
```

### Pull-Request-Prozess

#### Branch-Strategie

```bash
# Neuen Feature-Branch erstellen
git checkout -b feature/auto-scaling

# Oder Bug-Fix-Branch
git checkout -b fix/worker-stop-issue

# Oder Dokumentations-Branch
git checkout -b docs/api-documentation
```

#### Commit-Nachrichten

Verwende das [Conventional Commits](https://www.conventionalcommits.org/) Format:

```bash
# Feature
git commit -m "feat: add auto-scaling for workers based on queue length"

# Bug-Fix
git commit -m "fix: resolve worker stop issue when dashboard is closed"

# Dokumentation
git commit -m "docs: add API documentation for worker management"

# Refactoring
git commit -m "refactor: extract worker scaling logic into separate service"

# Tests
git commit -m "test: add unit tests for auto-scaling service"

# Performance
git commit -m "perf: optimize database queries for queue statistics"

# Breaking Change
git commit -m "feat!: change worker configuration format

BREAKING CHANGE: Worker configuration now requires 'display_name' field"
```

#### Pull-Request-Template

```markdown
## Beschreibung
Kurze Beschreibung der Änderungen.

## Art der Änderung
- [ ] Bug-Fix (non-breaking change)
- [ ] Neues Feature (non-breaking change)
- [ ] Breaking Change (fix oder feature, das bestehende Funktionalität beeinflusst)
- [ ] Dokumentation
- [ ] Performance-Verbesserung
- [ ] Refactoring

## Wie wurde getestet?
- [ ] Unit Tests
- [ ] Integration Tests
- [ ] Manuelle Tests
- [ ] Browser Tests

## Checkliste
- [ ] Code folgt den Coding-Standards
- [ ] Self-Review durchgeführt
- [ ] Code ist kommentiert (besonders komplexe Bereiche)
- [ ] Dokumentation wurde aktualisiert
- [ ] Tests wurden hinzugefügt/aktualisiert
- [ ] Alle Tests bestehen
- [ ] Keine Merge-Konflikte

## Screenshots (falls UI-Änderungen)
Füge Screenshots hinzu, falls die Änderungen die UI betreffen.

## Zusätzliche Notizen
Weitere Informationen für die Reviewer.
```

## 🧪 Testing

### Test-Struktur

```
tests/
├── Unit/                    # Unit Tests
│   ├── Services/
│   ├── Models/
│   └── Commands/
├── Feature/                 # Feature Tests
│   ├── Dashboard/
│   ├── API/
│   └── Workers/
├── Integration/             # Integration Tests
│   ├── Database/
│   └── Queue/
└── Browser/                 # Browser Tests
    └── Dashboard/
```

### Test-Beispiele

#### Unit Test

```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use HenningD\LaravelQueueManager\Services\WorkerService;
use HenningD\LaravelQueueManager\Models\QueueWorker;

class WorkerServiceTest extends TestCase
{
    private WorkerService $service;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WorkerService();
    }
    
    /** @test */
    public function it_can_create_a_worker()
    {
        $config = [
            'name' => 'Test Worker',
            'queue' => 'default',
            'timeout' => 60,
        ];
        
        $worker = $this->service->createWorker($config);
        
        $this->assertInstanceOf(QueueWorker::class, $worker);
        $this->assertEquals('Test Worker', $worker->name);
        $this->assertEquals('default', $worker->queue);
        $this->assertEquals(60, $worker->timeout);
    }
    
    /** @test */
    public function it_validates_worker_configuration()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Worker name is required');
        
        $this->service->createWorker([]);
    }
}
```

#### Feature Test

```php
<?php

namespace Tests\Feature\API;

use Tests\TestCase;
use HenningD\LaravelQueueManager\Models\QueueWorker;

class WorkerAPITest extends TestCase
{
    /** @test */
    public function it_can_list_workers_via_api()
    {
        QueueWorker::factory()->count(3)->create();
        
        $response = $this->getJson('/queue-manager/api/workers');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'queue',
                            'status',
                            'created_at'
                        ]
                    ]
                ])
                ->assertJsonCount(3, 'data');
    }
    
    /** @test */
    public function it_can_create_worker_via_api()
    {
        $workerData = [
            'name' => 'API Test Worker',
            'queue' => 'test-queue',
            'timeout' => 120,
        ];
        
        $response = $this->postJson('/queue-manager/api/workers', $workerData);
        
        $response->assertStatus(201)
                ->assertJsonFragment([
                    'name' => 'API Test Worker',
                    'queue' => 'test-queue',
                ]);
                
        $this->assertDatabaseHas('queue_workers', $workerData);
    }
}
```

### Test-Commands

```bash
# Alle Tests ausführen
composer test

# Spezifische Test-Suite
composer test -- --testsuite=Unit
composer test -- --testsuite=Feature

# Tests mit Coverage
composer test-coverage

# Spezifische Test-Datei
composer test tests/Unit/Services/WorkerServiceTest.php

# Tests mit Filter
composer test -- --filter=WorkerService
```

## 📚 Dokumentation

### Wiki-Beiträge

#### Neue Wiki-Seite erstellen

1. **Datei erstellen**: `docs/wiki/Neue-Seite.md`
2. **Template verwenden**:

```markdown
# Seitentitel

Kurze Beschreibung der Seite.

## 🎯 Übersicht

Was behandelt diese Seite:

1. **Punkt 1** - Beschreibung
2. **Punkt 2** - Beschreibung

## Hauptinhalt

### Unterabschnitt

Inhalt hier...

## ➡️ Nächste Schritte

- [Verwandte Seite 1](Link1.md)
- [Verwandte Seite 2](Link2.md)
```

3. **In Home.md verlinken**
4. **Pull Request erstellen**

#### Code-Dokumentation

```php
/**
 * Queue Manager Service für erweiterte Queue-Operationen.
 * 
 * Diese Klasse bietet Methoden für:
 * - Worker-Management
 * - Queue-Konfiguration  
 * - Performance-Monitoring
 * - Auto-Scaling
 * 
 * @package HenningD\LaravelQueueManager\Services
 * @author Dein Name <email@example.com>
 * @since 1.0.0
 * 
 * @example
 * ```php
 * $service = new QueueManagerService();
 * $worker = $service->createWorker('emails', ['timeout' => 60]);
 * $service->startWorker($worker);
 * ```
 */
class QueueManagerService
{
    // Implementation...
}
```

## 🌍 Community-Support

### Diskussionen und Hilfe

1. **GitHub Discussions** - Für allgemeine Fragen und Diskussionen
2. **Issues** - Für spezifische Probleme und Bug-Reports
3. **Pull Requests** - Für Code-Reviews und Diskussionen

### Hilfe anbieten

- **Issues beantworten** - Hilf anderen bei Problemen
- **Code-Reviews** - Reviewe Pull Requests
- **Dokumentation verbessern** - Ergänze fehlende Informationen
- **Beispiele erstellen** - Teile Anwendungsbeispiele

## 🏆 Anerkennung

### Contributors

Alle Beiträge werden in der `CONTRIBUTORS.md` Datei anerkannt:

```markdown
# Contributors

Vielen Dank an alle, die zu diesem Projekt beigetragen haben:

## Core Team
- [@henningd](https://github.com/henningd) - Projekt-Gründer und Maintainer

## Contributors
- [@contributor1](https://github.com/contributor1) - Feature XYZ
- [@contributor2](https://github.com/contributor2) - Bug-Fixes und Tests
- [@contributor3](https://github.com/contributor3) - Dokumentation

## Special Thanks
- [@helper1](https://github.com/helper1) - Community Support
- [@helper2](https://github.com/helper2) - Testing und Feedback
```

### Badges und Anerkennung

- **First-time Contributor** - Für den ersten Beitrag
- **Bug Hunter** - Für das Finden und Melden von Bugs
- **Documentation Hero** - Für umfangreiche Dokumentations-Beiträge
- **Code Quality Champion** - Für herausragende Code-Qualität

## 📞 Kontakt

### Maintainer

- **GitHub**: [@henningd](https://github.com/henningd)
- **E-Mail**: [maintainer@example.com](mailto:maintainer@example.com)

### Community

- **GitHub Discussions**: [Diskussionen](https://github.com/henningd/laravel-queue-manager/discussions)
- **Issues**: [Bug-Reports und Feature-Requests](https://github.com/henningd/laravel-queue-manager/issues)

## 📋 Checkliste für Beiträge

### Vor dem Beitrag

- [ ] Issue erstellt oder existierendes Issue kommentiert
- [ ] Fork des Repositories erstellt
- [ ] Lokale Entwicklungsumgebung eingerichtet
- [ ] Branch für den Beitrag erstellt

### Während der Entwicklung

- [ ] Coding-Standards befolgt
- [ ] Tests geschrieben/aktualisiert
- [ ] Dokumentation aktualisiert
- [ ] Commit-Nachrichten folgen Konventionen

### Vor dem Pull Request

- [ ] Alle Tests bestehen
- [ ] Code-Style-Checks bestehen
- [ ] Self-Review durchgeführt
- [ ] Branch ist aktuell mit main/master

### Pull Request

- [ ] Aussagekräftiger Titel und Beschreibung
- [ ] Template vollständig ausgefüllt
- [ ] Screenshots hinzugefügt (bei UI-Änderungen)
- [ ] Breaking Changes dokumentiert

Vielen Dank für deinen Beitrag zum Laravel Queue Manager! 🚀