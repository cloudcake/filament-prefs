<?php

namespace Cloudcake\Prefs\Schemas;

use Filament\Auth\MultiFactor\Contracts\MultiFactorAuthenticationProvider;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserSecuritySchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            static::getPasswordContentComponent(),
            static::getMultiFactorAuthenticationContentComponent()
        ]);
    }

    protected static function getPasswordContentComponent(): Component
    {
        return Section::make('Change Password')
                      ->icon('tabler-password')
                      ->schema([
                          static::getPasswordFormComponent(),
                          static::getPasswordConfirmationFormComponent(),
                          static::getCurrentPasswordFormComponent(),
                      ]);
    }

    protected static function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
                        ->label(__('filament-panels::auth/pages/edit-profile.form.password.label'))
                        ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.password.validation_attribute'))
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->rule(Password::default())
                        ->showAllValidationMessages()
                        ->autocomplete('new-password')
                        ->dehydrated(fn($state): bool => filled($state))
                        ->dehydrateStateUsing(fn($state): string => Hash::make($state))
                        ->live(debounce: 500)
                        ->same('passwordConfirmation');
    }

    protected static function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
                        ->label(__('filament-panels::auth/pages/edit-profile.form.password_confirmation.label'))
                        ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.password_confirmation.validation_attribute'))
                        ->password()
                        ->revealable(filament()->arePasswordsRevealable())
                        ->required()
                        ->visible(fn(Get $get): bool => filled($get('password')))
                        ->dehydrated(false);
    }

    protected static function getCurrentPasswordFormComponent(): Component
    {
        return TextInput::make('currentPassword')
                        ->label(__('filament-panels::auth/pages/edit-profile.form.current_password.label'))
                        ->validationAttribute(__('filament-panels::auth/pages/edit-profile.form.current_password.validation_attribute'))
                        ->belowContent(__('filament-panels::auth/pages/edit-profile.form.current_password.below_content'))
                        ->password()
                        ->currentPassword(guard: Filament::getAuthGuard())
                        ->revealable(filament()->arePasswordsRevealable())
                        ->required()
                        ->visible(fn(Get $get): bool => filled($get('password')))
                        ->dehydrated(false);
    }

    protected static function getMultiFactorAuthenticationContentComponent(): ?Component
    {
        if (!Filament::hasMultiFactorAuthentication()) {
            return Grid::make();
        }

        $schema = collect(Filament::getMultiFactorAuthenticationProviders())
            ->sort(fn(MultiFactorAuthenticationProvider $multiFactorAuthenticationProvider): int => $multiFactorAuthenticationProvider->isEnabled(auth()->user()) ? 0 : 1)
            ->map(fn(MultiFactorAuthenticationProvider $multiFactorAuthenticationProvider): Component => Group::make($multiFactorAuthenticationProvider
                ->getManagementSchemaComponents())->statePath($multiFactorAuthenticationProvider->getId())
            )
            ->all();

        return Section::make('Multi-Factor Authentication')
                      ->icon('tabler-auth-2fa')
                      ->schema($schema);
    }
}