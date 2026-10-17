<?php

namespace Cloudcake\Prefs\Actions;

use Cloudcake\Prefs\Schemas\UserProfileSchema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;

class UserProfileAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'userProfile')
                     ->slideOver()
                     ->modalWidth(Width::ExtraLarge)
                     ->schema(fn($schema) => UserProfileSchema::configure($schema))
                     ->action(function (array $data) {
                         $user = auth()->user();
                         $user->fill($data);
                         $user->save();

                         Notification::make()
                                     ->success()
                                     ->title('Profile updated')
                                     ->body('Your profile has been updated successfully.')
                                     ->send();
                     });
    }
}