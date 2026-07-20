<?php

namespace Cloudcake\Prefs\Actions;

use Cloudcake\Prefs\Schemas\UserSecuritySchema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;

class UserSecurityAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'userSecurity')
                     ->slideOver()
                     ->modalWidth(Width::ExtraLarge)
                     ->action(function () {
                         Notification::make()
                                     ->success()
                                     ->title('Security updated')
                                     ->body('Your security settings have been updated successfully.')
                                     ->send();
                     })
                     ->schema(fn($schema) => UserSecuritySchema::configure($schema));
    }
}