# Dashboard Übersicht

Das Dashboard ist die zentrale Benutzeroberfläche des Laravel Queue Manager Packages. Es bietet einen umfassenden Überblick über deine Queue-Aktivitäten und ermöglicht die Verwaltung von Workers und Queues.

## 🎯 Dashboard-Zugriff

Das Dashboard erreichst du über:
- **URL**: `http://your-app.com/queue-manager`
- **Route Name**: `queue-manager.dashboard`

## 📊 Dashboard-Bereiche

### 1. Statistik-Karten

Das Dashboard zeigt vier Hauptstatistiken in übersichtlichen Karten:

#### 📈 Aktive Jobs
- **Beschreibung**: Anzahl der aktuell verarbeiteten Jobs
- **Farbe**: Blau
- **Icon**: Pfeil nach oben
- **Aktualisierung**: Echtzeit

#### ⏳ Wartende Jobs
- **Beschreibung**: Jobs in der Warteschlange
- **Farbe**: Gelb
- **Icon**: Uhr
- **Aktualisierung**: Echtzeit

#### ✅ Erfolgreiche Jobs
- **Beschreibung**: Erfolgreich abgeschlossene Jobs (letzte 24h)
- **Farbe**: Grün
- **Icon**: Häkchen
- **Zeitraum**: Letzte 24 Stunden

#### ❌ Fehlgeschlagene Jobs
- **Beschreibung**: Jobs mit Fehlern (letzte 24h)
- **Farbe**: Rot
- **Icon**: X-Symbol
- **Zeitraum**: Letzte 24 Stunden

### 2. Navigation

#### Sidebar-Navigation
Die responsive Sidebar bietet Zugriff auf alle Hauptbereiche:

- **🏠 Dashboard** - Hauptübersicht
- **👷 Workers** - Worker-Verwaltung
- **📋 Queues** - Queue-Management
- **📊 Jobs** - Job-Monitoring
- **⚙️ Einstellungen** - Konfiguration

#### Mobile Navigation
- **Hamburger-Menü**: Für mobile Geräte
- **Responsive Design**: Optimiert für alle Bildschirmgrößen
- **Touch-freundlich**: Große Buttons und einfache Navigation

### 3. Worker-Übersicht

#### Worker-Tabelle
Zeigt alle aktiven Workers mit folgenden Informationen:

| Spalte | Beschreibung |
|--------|--------------|
| **ID** | Eindeutige Worker-Identifikation |
| **Queue** | Zugewiesene Queue |
| **Status** | Aktiv/Inaktiv/Pausiert |
| **Gestartet** | Startzeit des Workers |
| **Verarbeitete Jobs** | Anzahl verarbeiteter Jobs |
| **Aktionen** | Stoppen/Neustarten/Löschen |

#### Worker-Aktionen
- **▶️ Starten**: Neuen Worker starten
- **⏸️ Pausieren**: Worker temporär anhalten
- **🔄 Neustarten**: Worker neu starten
- **🗑️ Löschen**: Worker entfernen

### 4. Queue-Übersicht

#### Queue-Tabelle
Übersicht aller konfigurierten Queues:

| Spalte | Beschreibung |
|--------|--------------|
| **Name** | Queue-Bezeichnung |
| **Wartende Jobs** | Anzahl Jobs in Warteschlange |
| **Aktive Jobs** | Aktuell verarbeitete Jobs |
| **Fehlgeschlagen** | Anzahl fehlgeschlagener Jobs |
| **Durchsatz** | Jobs pro Minute |
| **Aktionen** | Leeren/Pausieren/Löschen |

#### Queue-Aktionen
- **➕ Neue Queue**: Queue hinzufügen
- **🧹 Leeren**: Alle Jobs aus Queue entfernen
- **⏸️ Pausieren**: Queue-Verarbeitung stoppen
- **🔄 Wiederaufnehmen**: Pausierte Queue aktivieren

## 🔄 Echtzeit-Updates

### Auto-Refresh
- **Intervall**: 5 Sekunden (konfigurierbar)
- **AJAX-Updates**: Ohne Seitenneuladen
- **Indikator**: Ladeanimation während Updates

### Manuelle Aktualisierung
- **Refresh-Button**: Sofortige Aktualisierung
- **Keyboard-Shortcut**: `F5` oder `Ctrl+R`

## 🎨 Benutzeroberfläche

### Design-Prinzipien
- **Modern**: Tailwind CSS mit professionellem Design
- **Responsive**: Mobile-first Ansatz
- **Zugänglich**: ARIA-Labels und Keyboard-Navigation
- **Konsistent**: Einheitliche Farbpalette und Typografie

### Farbschema
- **Primär**: Blau (#3B82F6)
- **Erfolg**: Grün (#10B981)
- **Warnung**: Gelb (#F59E0B)
- **Fehler**: Rot (#EF4444)
- **Neutral**: Grau-Töne

### Interaktive Elemente
- **Hover-Effekte**: Sanfte Übergänge
- **Loading-States**: Spinner und Skeleton-Loader
- **Tooltips**: Hilfreiche Zusatzinformationen
- **Modals**: Für Bestätigungen und Formulare

## 📱 Mobile Optimierung

### Responsive Breakpoints
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px
- **Desktop**: > 1024px

### Mobile Features
- **Touch-Gesten**: Wischen und Tippen
- **Optimierte Tabellen**: Horizontales Scrollen
- **Große Buttons**: Mindestens 44px Höhe
- **Lesbare Schrift**: Mindestens 16px Größe

## ⚡ Performance

### Optimierungen
- **Lazy Loading**: Bilder und Komponenten
- **Caching**: Browser-Cache für statische Assets
- **Minimierte Assets**: CSS und JavaScript komprimiert
- **CDN-Ready**: Für externe Asset-Delivery

### Ladezeiten
- **Erste Ansicht**: < 2 Sekunden
- **AJAX-Updates**: < 500ms
- **Navigation**: < 100ms

## 🔧 Anpassungen

### Konfiguration
In `config/queue-manager.php`:

```php
'dashboard' => [
    'refresh_interval' => 5000, // Millisekunden
    'items_per_page' => 25,
    'show_statistics' => true,
    'enable_auto_refresh' => true,
],
```

### CSS-Anpassungen
Überschreibe Styles in deiner `app.css`:

```css
/* Dashboard-spezifische Anpassungen */
.queue-manager-dashboard {
    --primary-color: #your-color;
    --success-color: #your-color;
}
```

### JavaScript-Hooks
Erweitere Funktionalität:

```javascript
// Dashboard-Events abonnieren
document.addEventListener('queue-manager:updated', function(event) {
    console.log('Dashboard aktualisiert:', event.detail);
});
```

## 🚨 Fehlerbehebung

### Häufige Probleme

#### Dashboard lädt nicht
**Ursachen**:
- Route nicht registriert
- Middleware-Probleme
- Asset-Dateien fehlen

**Lösungen**:
```bash
php artisan route:clear
php artisan view:clear
php artisan queue-manager:install
```

#### Statistiken zeigen falsche Werte
**Ursachen**:
- Cache-Probleme
- Datenbankverbindung
- Queue-Konfiguration

**Lösungen**:
```bash
php artisan cache:clear
php artisan queue:restart
```

#### Mobile Ansicht funktioniert nicht
**Ursachen**:
- CSS nicht geladen
- JavaScript-Fehler
- Viewport-Meta-Tag fehlt

**Lösungen**:
- Browser-Cache leeren
- Entwicklertools prüfen
- Asset-Kompilierung überprüfen

## 📊 Monitoring

### Metriken
Das Dashboard zeigt folgende Metriken:
- **Durchsatz**: Jobs pro Minute/Stunde
- **Latenz**: Durchschnittliche Verarbeitungszeit
- **Fehlerrate**: Prozentsatz fehlgeschlagener Jobs
- **Queue-Länge**: Anzahl wartender Jobs

### Alerts
Automatische Benachrichtigungen bei:
- Hoher Fehlerrate (> 5%)
- Langen Warteschlangen (> 100 Jobs)
- Worker-Ausfällen
- Performance-Problemen

## ➡️ Nächste Schritte

- [Worker Management](Worker-Management.md) - Detaillierte Worker-Verwaltung
- [Queue Management](Queue-Management.md) - Queue-Konfiguration und -Überwachung
- [Job Monitoring](Job-Monitoring.md) - Einzelne Jobs verfolgen und debuggen