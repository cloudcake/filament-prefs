<?php

namespace Cloudcake\Prefs\Schemas;

use Cloudcake\Prefs\Preferences;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class UserProfileSchema
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            static::getGeneralProfileContentComponent(),
        ]);
    }

    protected static function getGeneralProfileContentComponent(): Component
    {
        return Group::make([
            static::getNameFormComponent()->columnSpan(1),
            static::getEmailFormComponent()->columnSpan(2),
        ])->columns(2)->columnSpan(2);
    }

    protected static function getNameFormComponent(): Component
    {
        return TextInput::make('name')
                        ->label(__('filament-panels::auth/pages/edit-profile.form.name.label'))
                        ->required()
                        ->default(auth()->user()->name)
                        ->maxLength(255)
                        ->autofocus();
    }

    protected static function getEmailFormComponent(): Component
    {
        return TextInput::make('email')
                        ->label(__('filament-panels::auth/pages/edit-profile.form.email.label'))
                        ->email()
                        ->required()
                        ->disabled()
                        ->default(auth()->user()->email)
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->live(debounce: 500);
    }
}