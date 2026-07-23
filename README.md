![banner](./banner.jpeg)

## Installation

### Add the cloudcake composer registry to your project's composer.json file:

```json
"repositories": [
    {
        "type": "composer",
        "url": "https://composer.cloudcake.dev"
    }
]
```

### Install the package via Composer

`composer require cloudcake/filament-prefs`

## Quickstart

```php
use Cloudcake\Prefs\Preferences;
use Filament\Panel;

public function panel(Panel $panel): Panel
{
    //...
    
    $panel->plugins([
        Preferences::make()
               ->profileUserMenuItem()
               ->preferencesUserMenuItem()
               ->securityUserMenuItem()
    ])
}
```