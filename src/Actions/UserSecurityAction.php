<?php

namespace Cloudcake\Prefs\Actions;

use Cloudcake\Prefs\Schemas\UserSecuritySchema;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Width;
use Illuminate\Auth\Events\PasswordReset;

class UserSecurityAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'userSecurity')
            ->slideOver()
            ->modalWidth(Width::ExtraLarge)
            ->action(function (array $data) {
                $user = auth()->user();

                if (!blank($data['password'] ?? null)) {
                    $user->password = $data['password'];

                    event(new PasswordReset($user));
                }

                $user->save();

                Notification::make()
                    ->success()
                    ->title('Security updated')
                    ->body('Your security settings have been updated successfully.')
                    ->send();
            })
            ->schema(fn($schema) => UserSecuritySchema::configure($schema));
    }
}