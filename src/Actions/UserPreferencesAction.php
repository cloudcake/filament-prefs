<?php

namespace Cloudcake\Prefs\Actions;

use Cloudcake\Prefs\Models\UserPreference;
use Cloudcake\Prefs\Schemas\UserPreferencesSchema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;

class UserPreferencesAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'userPreferences')
                     ->slideOver()
                     ->modalWidth(Width::ThreeExtraLarge)
                     ->schema(fn($schema) => UserPreferencesSchema::configure($schema))
                     ->action(function (array $data, $livewire) {
                         $config = UserPreference::current();
                         $config->config = $data;
                         $config->save();

                         Notification::make()
                                     ->success()
                                     ->title('Preferences updated')
                                     ->body('Your preferences have been updated successfully.')
                                     ->send();

                         $livewire->js('window.location.reload();');
                     });
    }
}