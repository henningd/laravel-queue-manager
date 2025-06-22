# Lokale Installation des Laravel Queue Manager Packages

## Methode 1: Über lokalen Pfad

In der `composer.json` des Zielprojekts:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../laravel-queue-manager"
        }
    ],
    "require": {
        "henningd/laravel-queue-manager": "*"
    }
}
```

Dann:
```bash
composer install
```

## Methode 2: Über Git Repository

In der `composer.json` des Zielprojekts:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/henningd/laravel-queue-manager.git"
        }
    ],
    "require": {
        "henningd/laravel-queue-manager": "dev-main"
    }
}
```

Dann:
```bash
composer install
```

## Methode 3: Symlink (für Entwicklung)

```bash
# Im Zielprojekt
cd vendor
mkdir -p henningd
ln -s /pfad/zum/laravel-queue-manager henningd/laravel-queue-manager
```

Dann in der `composer.json` des Zielprojekts die PSR-4 Autoload-Regel hinzufügen:

```json
{
    "autoload": {
        "psr-4": {
            "HenningD\\LaravelQueueManager\\": "vendor/henningd/laravel-queue-manager/src/"
        }
    }
}
```

Und Autoloader neu generieren:
```bash
composer dump-autoload
```

## Nach der Installation

1. Service Provider registrieren (falls nicht automatisch):
```php
// config/app.php
'providers' => [
    // ...
    HenningD\LaravelQueueManager\QueueManagerServiceProvider::class,
],
```

2. Konfiguration veröffentlichen:
```bash
php artisan vendor:publish --provider="HenningD\LaravelQueueManager\QueueManagerServiceProvider"
```

3. Migrationen ausführen:
```bash
php artisan migrate