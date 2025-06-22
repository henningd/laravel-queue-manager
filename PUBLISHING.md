# Package auf Packagist veröffentlichen

## Voraussetzungen

1. Git Repository (GitHub, GitLab, etc.)
2. Account auf [Packagist.org](https://packagist.org)

## Schritte

### 1. Repository vorbereiten

```bash
# Repository initialisieren (falls noch nicht geschehen)
git init
git add .
git commit -m "Initial commit"

# Remote Repository hinzufügen
git remote add origin https://github.com/henningd/laravel-queue-manager.git
git push -u origin main
```

### 2. Version taggen

```bash
# Erste Version erstellen
git tag v1.0.0
git push origin v1.0.0
```

### 3. Auf Packagist veröffentlichen

1. Gehe zu [Packagist.org](https://packagist.org)
2. Registriere dich oder melde dich an
3. Klicke auf "Submit"
4. Gib die Repository URL ein: `https://github.com/henningd/laravel-queue-manager`
5. Klicke auf "Check"

### 4. Auto-Update einrichten

Für automatische Updates bei neuen Tags:
1. Gehe zu deinem Package auf Packagist
2. Klicke auf "Settings"
3. Aktiviere "Auto-update"

## Neue Versionen veröffentlichen

```bash
# Änderungen committen
git add .
git commit -m "Update: neue Features"

# Neue Version taggen
git tag v1.1.0
git push origin v1.1.0
```

Packagist wird automatisch die neue Version erkennen.