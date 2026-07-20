<?php

namespace Cloudcake\Prefs;

use Cloudcake\Prefs\Actions\UserPreferencesAction;
use Cloudcake\Prefs\Actions\UserProfileAction;
use Cloudcake\Prefs\Actions\UserSecurityAction;
use Cloudcake\Prefs\Enums\PreferenceOption;
use Cloudcake\Prefs\Models\UserPreference;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Notifications\Livewire\Notifications;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Closure;
use Filament\Contracts\Plugin;
use Filament\Panel;

class Preferences implements Plugin
{
    private Closure|bool $enabled = true;
    private Closure|bool $profileUserMenuItemEnabled = false;
    private Closure|string|\BackedEnum|\UnitEnum|null $profileUserMenuItemIcon = null;
    private Closure|string|null $profileUserMenuItemLabel = null;
    private Closure|string|null $profileDescription = null;
    private Closure|bool $securityUserMenuItemEnabled = false;
    private Closure|string|\BackedEnum|\UnitEnum|null $securityUserMenuItemIcon = null;
    private Closure|string|null $securityUserMenuItemLabel = null;
    private Closure|string|null $securityDescription = null;
    private Closure|bool $preferencesUserMenuItemEnabled = false;
    private Closure|string|\BackedEnum|\UnitEnum|null $preferencesUserMenuItemIcon = null;
    private Closure|string|null $preferencesUserMenuItemLabel = null;
    private Closure|string|null $preferencesDescription = null;

    /**
     * @return static
     */
    public static function make(): static
    {
        return app(static::class);
    }

    public function getId(): string
    {
        return 'cloudcake-preferences';
    }

    public function register(Panel $panel): void
    {
        if (!$panel->evaluate($this->enabled))
            return;

        $this->configureComponents($panel);

        $panel->userMenuItems([
            UserProfileAction::make()
                             ->modalIcon($panel->evaluate($this->profileUserMenuItemIcon))
                             ->modalHeading($panel->evaluate($this->profileUserMenuItemLabel))
                             ->modalDescription($panel->evaluate($this->profileDescription))
                             ->label(fn() => $panel->evaluate($this->profileUserMenuItemLabel))
                             ->icon(fn() => $panel->evaluate($this->profileUserMenuItemIcon))
                             ->visible(fn() => $panel->evaluate($this->profileUserMenuItemEnabled)),

            UserPreferencesAction::make()
                                 ->modalIcon($panel->evaluate($this->preferencesUserMenuItemIcon))
                                 ->modalHeading($panel->evaluate($this->preferencesUserMenuItemLabel))
                                 ->modalDescription($panel->evaluate($this->preferencesDescription))
                                 ->label(fn() => $panel->evaluate($this->preferencesUserMenuItemLabel))
                                 ->icon(fn() => $panel->evaluate($this->preferencesUserMenuItemIcon))
                                 ->visible(fn() => $panel->evaluate($this->preferencesUserMenuItemEnabled)),

            UserSecurityAction::make()
                              ->modalIcon($panel->evaluate($this->securityUserMenuItemIcon))
                              ->modalHeading($panel->evaluate($this->securityUserMenuItemLabel))
                              ->modalDescription($panel->evaluate($this->securityDescription))
                              ->label(fn() => $panel->evaluate($this->securityUserMenuItemLabel))
                              ->icon(fn() => $panel->evaluate($this->securityUserMenuItemIcon))
                              ->visible(fn() => $panel->evaluate($this->securityUserMenuItemEnabled)),
        ]);
    }

    public function boot(Panel $panel): void
    {
    }

    public function apply(Closure|bool $condition = true): static
    {
        $this->enabled = $condition;

        return $this;
    }

    public function profileUserMenuItem(
        Closure|bool                      $condition = true,
        \BackedEnum|\UnitEnum|string|null $icon = 'tabler-user',
        string|null                       $label = 'Profile',
        string|null                       $description = 'Customize your profile.'
    ): static
    {
        $this->profileUserMenuItemEnabled = $condition;
        $this->profileUserMenuItemIcon = $icon;
        $this->profileUserMenuItemLabel = $label;
        $this->profileDescription = $description;

        return $this;
    }

    public function preferencesUserMenuItem(
        Closure|bool                      $condition = true,
        \BackedEnum|\UnitEnum|string|null $icon = 'tabler-settings',
        string|null                       $label = 'Preferences',
        string|null                       $description = 'Customize your panel preferences. These settings will be saved across all panels and devices.'
    ): static
    {
        $this->preferencesUserMenuItemEnabled = $condition;
        $this->preferencesUserMenuItemIcon = $icon;
        $this->preferencesUserMenuItemLabel = $label;
        $this->preferencesDescription = $description;

        return $this;
    }

    public function securityUserMenuItem(
        Closure|bool                      $condition = true,
        \BackedEnum|\UnitEnum|string|null $icon = 'tabler-shield-lock',
        string|null                       $label = 'Security',
        string|null                       $description = 'Modify your security settings.'
    ): static
    {
        $this->securityUserMenuItemEnabled = $condition;
        $this->securityUserMenuItemIcon = $icon;
        $this->securityUserMenuItemLabel = $label;
        $this->securityDescription = $description;

        return $this;
    }

    private function configureComponents(): void
    {
        static $config = false;

        $getUserSetting = function (PreferenceOption $pref, $default = null) use (&$config) {
            if (!$config)
                $config = UserPreference::current()->config ?? [];

            $data = data_get($config, $pref->name, $default);

            return is_null($data) ? $default : $data;
        };

        $timezone = fn() => $getUserSetting(PreferenceOption::Timezone, 'Africa/Johannesburg');
        $dateFormat = fn() => $getUserSetting(PreferenceOption::DateFormat, 'M j, Y');
        $timeFormat = fn() => $getUserSetting(PreferenceOption::TimeFormat, 'H:i');
        $dialogsAsSlides = fn() => $getUserSetting(PreferenceOption::DialogDisplayMode, 'modals') === 'slide';
        $dialogsCloseOnEscape = fn() => $getUserSetting(PreferenceOption::DialogCloseEscape, false);
        $dialogsCloseOnOutside = fn() => $getUserSetting(PreferenceOption::DialogCloseOutside, false);
        $rememberTableColumns = fn() => $getUserSetting(PreferenceOption::TablePersistColumns, false);
        $rememberTableFilters = fn() => $getUserSetting(PreferenceOption::TablePersistFilters, false);
        $rememberTableSearches = fn() => $getUserSetting(PreferenceOption::TablePersistSearch, false);
        $tablesStriped = fn() => $getUserSetting(PreferenceOption::TableStripedRows, false);

        TextColumn::configureUsing(modifyUsing: fn(TextColumn $component) => $component
            ->timezone(fn() => $timezone())
            ->when(fn(TextColumn $component) => $component->isDate(), fn(TextColumn $component) => $component->date(fn() => $dateFormat()))
            ->when(fn(TextColumn $component) => $component->isTime(), fn(TextColumn $component) => $component->time(fn() => $timeFormat()))
            ->when(fn(TextColumn $component) => $component->isDateTime(), fn(TextColumn $component) => $component->dateTime(fn() => $dateFormat() . ' ' . $timeFormat()))
        );

        DatePicker::configureUsing(fn(DatePicker $component) => $component
            ->displayFormat(fn() => $dateFormat())
        );

        TimePicker::configureUsing(fn(TimePicker $component) => $component
            ->displayFormat(fn() => $timeFormat())
        );

        DateTimePicker::configureUsing(fn(DateTimePicker $component) => $component
            ->displayFormat(fn() => $dateFormat() . ' ' . $timeFormat())
        );

        Schema::configureUsing(fn(Schema $component) => $component
            ->defaultDateDisplayFormat(fn() => $dateFormat())
            ->defaultTimeDisplayFormat(fn() => $timeFormat())
            ->defaultDateTimeDisplayFormat(fn() => $dateFormat() . ' ' . $timeFormat())
        );

        Action::configureUsing(fn(Action $action) => $action
            ->closeModalByEscaping(fn() => $dialogsCloseOnEscape())
            ->closeModalByClickingAway(fn() => $dialogsCloseOnOutside())
            ->slideOver(fn() => $dialogsAsSlides())
        );

        CreateAction::configureUsing(fn(CreateAction $action) => $action
            ->slideOver(fn() => $dialogsAsSlides())
        );

        DeleteAction::configureUsing(fn(DeleteAction $action) => $action
            ->slideOver(fn() => $dialogsAsSlides())
        );

        EditAction::configureUsing(fn(EditAction $action) => $action
            ->slideOver(fn() => $dialogsAsSlides())
        );

        ViewAction::configureUsing(fn(ViewAction $action) => $action
            ->slideOver(fn() => $dialogsAsSlides())
            ->modalWidth(fn(Action $action) => $action->isModalSlideOver() ? Width::ExtraLarge : Width::TwoExtraLarge)
        );

        Table::configureUsing(fn(Table $component) => $component
            ->persistSearchInSession(fn() => $rememberTableSearches())
            ->persistColumnsInSession(fn() => $rememberTableColumns())
            ->persistColumnSearchesInSession(fn() => $rememberTableColumns())
            ->persistFiltersInSession(fn() => $rememberTableFilters())
            ->defaultDateDisplayFormat(fn() => $dateFormat())
            ->defaultTimeDisplayFormat(fn() => $timeFormat())
            ->defaultDateTimeDisplayFormat(fn() => $dateFormat() . ' ' . $timeFormat())
            ->striped(fn() => $tablesStriped())
        );

        Notification::configureUsing(function (Notification $notification) use ($getUserSetting) {
            Notifications::alignment(
                Alignment::tryFrom(strtolower($getUserSetting(PreferenceOption::NotificationHorizontalPosition))) ?? Alignment::Center
            );

            Notifications::verticalAlignment(
                VerticalAlignment::tryFrom(strtolower($getUserSetting(PreferenceOption::NotificationVerticalPosition))) ?? VerticalAlignment::Start
            );

            $notification->duration(fn() => intval($getUserSetting(PreferenceOption::NotificationDuration, 6) * 1000));
        });
    }

    public static function getTableName(): string
    {
        return config('prefs.migration.table.preferences', 'user_preferences');
    }
}
