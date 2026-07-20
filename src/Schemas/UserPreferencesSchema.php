<?php

namespace Cloudcake\Prefs\Schemas;

use Cloudcake\Prefs\Enums\PreferenceOption;
use Cloudcake\Prefs\Models\UserPreference;
use DateTimeZone;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\VerticalAlignment;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class UserPreferencesSchema
{
    public static function configure(Schema $schema): Schema
    {
        $data = UserPreference::current()->config;

        return $schema
            ->state($data)
            ->components([
                Section::make('Notifications')
                       ->description('These settings affect the appearance of notifications and behaviour of notification popups.')
                       ->icon(Heroicon::BellAlert)
                       ->schema([
                           Group::make([
                               Select::make(PreferenceOption::NotificationHorizontalPosition->name)
                                     ->default($data[PreferenceOption::NotificationHorizontalPosition->name] ?? null)
                                     ->live()
                                     ->label('Horizontal Position')
                                     ->placeholder('Default')
                                     ->options([
                                         Alignment::Start->name  => 'Left',
                                         Alignment::Center->name => 'Center',
                                         Alignment::End->name    => 'Right',
                                     ]),

                               Select::make(PreferenceOption::NotificationVerticalPosition->name)
                                     ->default($data[PreferenceOption::NotificationVerticalPosition->name] ?? null)
                                     ->live()
                                     ->label('Vertical Position')
                                     ->placeholder('Default')
                                     ->options([
                                         VerticalAlignment::Start->name  => 'Top',
                                         VerticalAlignment::Center->name => 'Center',
                                         VerticalAlignment::End->name    => 'Bottom',
                                     ]),

                               TextInput::make(PreferenceOption::NotificationDuration->name)
                                        ->default($data[PreferenceOption::NotificationDuration->name] ?? 6)
                                        ->label('Duration')
                                        ->suffix('seconds')
                                        ->minValue(0.5)
                                        ->numeric()
                                        ->required()
                                        ->live()
                           ])->columns(3),
                       ]),

                Section::make('Date & Time')
                       ->description('These settings affect the formatting of dates and times across the interface, this includes offsetting date/time based on the selected timezone.')
                       ->icon(Heroicon::Clock)
                       ->schema([
                           Group::make([
                               Select::make(PreferenceOption::Timezone->name)
                                     ->label('Preferred Timezone')
                                     ->belowContent('All dates & times will be displayed in this timezone.')
                                     ->searchable()
                                     ->placeholder('UTC')
                                     ->selectablePlaceholder()
                                     ->default($data[PreferenceOption::Timezone->name] ?? 'UTC')
                                     ->options(fn() => collect(DateTimeZone::listIdentifiers())
                                         ->flatMap(function ($zone) {
                                             $timezone = now()->setTimezone($zone)->format('p');

                                             return [
                                                 $zone => str("GMT$timezone ")->append(str($zone)->after('/'))->replace('_', ' ')
                                             ];
                                         })
                                         ->sortBy(fn($value, $key) => $value)
                                         ->all()
                                     ),
                           ])->columnSpanFull()->columns(),

                           Group::make([
                               Select::make(PreferenceOption::DateFormat->name)
                                     ->label('Preferred Date Format')
                                     ->placeholder('Use Default')
                                     ->default($data[PreferenceOption::DateFormat->name] ?? null)
                                     ->grow()
                                     ->options(function ($get) {
                                         $time = now()->setTimezone($get(PreferenceOption::Timezone->name) ?? 'UTC');

                                         $formats = [
                                             'd/m/Y',
                                             'd-m-Y',
                                             'd.m.Y',
                                             'Y-m-d',
                                             'Y.m.d',
                                             'Y/m/d',
                                             'm/d/Y',
                                             'jS F Y',
                                             'l, j F Y',
                                             'l, jS F Y',
                                             'l, jS F',
                                             'M j, Y',
                                             'M jS, Y',
                                             'F j, Y',
                                             'F jS, Y',
                                         ];

                                         return collect($formats)->flatMap(fn($format) => [$format => $time->format($format)])->all();
                                     }),

                               Select::make(PreferenceOption::TimeFormat->name)
                                     ->label('Preferred Time Format')
                                     ->placeholder('Use Default')
                                     ->default($data[PreferenceOption::TimeFormat->name] ?? null)
                                     ->options(function ($get) {
                                         $time = now()->setTimezone($get(PreferenceOption::Timezone->name) ?? 'UTC');

                                         $formats = [
                                             'H:i:s',
                                             'H:i:s A',
                                             'H:i:s a',
                                             'H:i',
                                             'h:i:s',
                                             'h:i:s A',
                                             'h:i A',
                                             'h:i a',
                                             'h:i',
                                             'g:i:s',
                                             'g:i:s A',
                                         ];

                                         return collect($formats)->flatMap(fn($format) => [$format => $time->format($format)])->all();
                                     })
                           ])->columnSpanFull()->columns([
                               'md' => 3, 'xl' => 3
                           ]),
                       ]),

                Section::make('Dialog Behaviours')
                       ->description('These settings affect the appearance of dialogs such as confirmation, action, record and filtering prompts. These settings may extend to further components when applying to all dialogs.')
                       ->icon(Heroicon::Window)
                       ->footerActions([
                           Action::make('test_dialogs')
                                 ->link()
                                 ->label('Preview')
                                 ->modal()
                                 ->slideOver(fn($get) => $get(PreferenceOption::DialogDisplayMode->name) === 'slide')
                                 ->closeModalByEscaping(fn($get) => $get(PreferenceOption::DialogCloseEscape->name))
                                 ->closeModalByClickingAway(fn($get) => $get(PreferenceOption::DialogCloseOutside->name))
                                 ->modalHeading("Dialog Mode Preview")
                                 ->modalDescription("This is a preview of the dialog's visual appearance.")
                                 ->modalWidth(Width::Large)
                                 ->modalSubmitAction(false)
                                 ->modalCancelActionLabel('Dismiss')
                                 ->schema([])
                       ])
                       ->schema([
                           Fieldset::make('Preferred Dialog Display')->columns(1)->schema([
                               Radio::make(PreferenceOption::DialogDisplayMode->name)
                                    ->default($data[PreferenceOption::DialogDisplayMode->name] ?? 'modal')
                                    ->hiddenLabel()
                                    ->live()
                                    ->options([
                                        'modal' => 'Modals',
                                        'slide' => 'Slideovers'
                                    ])
                                    ->descriptions([
                                        'modal' => 'Modals are full screen overlays that block the page content.',
                                        'slide' => 'Slideovers are overlays that slide in from the side of the screen.'
                                    ]),
                           ]),

                           Fieldset::make('Close Behaviour')
                                   ->columns(1)
                                   ->schema([
                                       Toggle::make(PreferenceOption::DialogCloseEscape->name)
                                             ->default($data[PreferenceOption::DialogCloseEscape->name] ?? false)
                                             ->live()
                                             ->label('Close dialogs when the escape key is pressed.'),

                                       Toggle::make(PreferenceOption::DialogCloseOutside->name)
                                             ->default($data[PreferenceOption::DialogCloseOutside->name] ?? false)
                                             ->live()
                                             ->label('Close dialogs when the area outside the dialog is clicked.')
                                   ])
                       ]),

                Section::make('Table Behaviours')
                       ->description('These settings affect the behaviour of tables such as filters, searches and column persistence and some visual tweaks.')
                       ->icon(Heroicon::TableCells)
                       ->schema([
                           Toggle::make(PreferenceOption::TablePersistFilters->name)
                                 ->default($data[PreferenceOption::TablePersistFilters->name] ?? false)
                                 ->label('Remember & persist table filters.'),

                           Toggle::make(PreferenceOption::TablePersistSearch->name)
                                 ->default($data[PreferenceOption::TablePersistSearch->name] ?? false)
                                 ->label('Remember & persist table searches.'),

                           Toggle::make(PreferenceOption::TablePersistColumns->name)
                                 ->default($data[PreferenceOption::TablePersistColumns->name] ?? false)
                                 ->label('Remember & persist table columns.'),

                           Toggle::make(PreferenceOption::TableStripedRows->name)
                                 ->default($data[PreferenceOption::TableStripedRows->name] ?? false)
                                 ->label('Use striped tables; every second row will be striped.'),
                       ]),
            ]);
    }
}