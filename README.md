# cloudcake/filament-prefs
User preferences for panel behaviour.

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