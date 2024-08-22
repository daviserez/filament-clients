<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientResource\Pages;
use App\Filament\Resources\ClientRessourceResource\RelationManagers\AppointmentsRelationManager;
use App\Helpers\Utils;
use App\Models\Client;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ClientResource extends Resource
{
    protected static ?string $model = Client::class;

    protected static ?string $navigationIcon = 'heroicon-m-user-circle';

    protected static ?string $recordTitleAttribute = 'full_name';

    protected static int $globalSearchResultsLimit = 10;

    protected static ?int $navigationSort = 1;

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        $strmail = $record->email ? " ($record->email)" : '';

        return "{$record->full_name}$strmail";
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'firstname', 'email'];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return ClientResource::getUrl('view', ['record' => $record]);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $since = $record->lastAppointment?->diffForHumans() ?? __('client.appointment.never');

        return [
            __('client.appointment.last_label') => $since,
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->whereNull('deleted_at');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Fieldset::make(__('client.edit.tab.contact'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('client.edit.name'))
                            ->required()
                            ->maxLength(255)
                            ->autocomplete(false)
                            ->autofocus(),
                        TextInput::make('firstname')
                            ->label(__('client.edit.firstname'))
                            ->autocomplete(false)
                            ->maxLength(255),
                        TextInput::make('primary_phone')
                            ->label(__('client.edit.primary_phone'))
                            ->autocomplete(false)
                            ->maxLength(255),
                        TextInput::make('secondary_phone')
                            ->label(__('client.edit.secondary_phone'))
                            ->autocomplete(false)
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('client.edit.email'))
                            ->autocomplete(false)
                            ->email()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Fieldset::make(__('client.edit.tab.address'))
                    ->schema([
                        TextInput::make('street')
                            ->label(__('client.edit.street'))
                            ->autocomplete(false)
                            ->maxLength(255)->columnSpanFull(),
                        TextInput::make('postcode')
                            ->label(__('client.edit.postcode'))
                            ->autocomplete(false)
                            ->maxLength(255),
                        TextInput::make('city')
                            ->label(__('client.edit.city'))
                            ->maxLength(255)
                            ->autocomplete(false)
                            ->columnSpan(3),
                        TextInput::make('country')
                            ->label(__('client.edit.country'))
                            ->maxLength(255)
                            ->columnSpan(2)
                            ->autocomplete(false),
                    ])->columns(6),
                Fieldset::make(__('client.edit.tab.other'))
                    ->schema([
                        ColorPicker::make('avatar_color')
                            ->label(__('client.edit.avatar_color'))
                            ->extraAttributes(['class' => 'justify-self-start']),
                        RichEditor::make('notes')
                            ->label(__('client.edit.notes'))
                            ->disableToolbarButtons([
                                'attachFiles',
                            ]),
                    ])
                    ->columns(1),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Split::make([
                    ViewColumn::make('avatar')
                        ->grow(false)
                        ->view('filament.tables.columns.avatar'),
                    Stack::make([
                        TextColumn::make('fullname')
                            ->label(__('client.table.sort.fullname'))
                            ->weight(FontWeight::Bold)
                            ->searchable(['name', 'firstname'])
                            ->sortable(['name', 'firstname'])
                            ->icon(fn (Model $record) => $record->trashed() ? 'heroicon-s-archive-box' : null)
                            ->iconColor('warning')
                            ->iconPosition(IconPosition::After),
                        TextColumn::make('lastAppointment')
                            ->color('gray')
                            ->placeholder(__('client.appointment.never_came'))
                            ->label(__('client.table.sort.last_appointment'))
                            ->sortable(query: function (Builder $query, string $direction): Builder {
                                // Make a subquery to retrieve the most recent appointment then sort by it.
                                $latestAppointments = DB::table('appointments')
                                    ->select('client_id', DB::raw('MAX(appointed_at) as last_appointment'))
                                    ->groupBy('client_id');

                                return $query
                                    ->select('clients.*')
                                    ->leftJoinSub($latestAppointments, 'latest_appointment', function (JoinClause $join) {
                                        $join->on('clients.id', '=', 'latest_appointment.client_id');
                                    })
                                    // Reverse the order because it seems more logical that ASC is the more recent appointment.
                                    ->orderBy('last_appointment', $direction === 'asc' ? 'desc' : 'asc');
                            })
                            ->since(),
                    ]),
                    Stack::make([
                        TextColumn::make('fullphone')
                            ->icon('heroicon-s-phone')
                            ->searchable(query: function (Builder $query, string $search): Builder {
                                $search = preg_replace('/[^a-zA-Z\d]/', '', $search);
                                if (empty($search)) {
                                    return $query->whereRaw('TRUE');
                                }

                                return $query
                                    ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`primary_phone`, ')', ''), '(', ''), '/', ''), '+', ''), ' ', '')  like '%{$search}%' OR REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`secondary_phone`, ')', ''), '(', ''), '/', ''), '+', ''), ' ', '')  like '%{$search}%'");
                            })
                            ->html(),

                        TextColumn::make('email')
                            ->icon('heroicon-s-envelope')
                            ->searchable(),
                    ]),
                    TextColumn::make('fulladress')
                        ->icon('heroicon-s-map-pin')
                        ->searchable()
                        ->html()
                        ->searchable(['street', 'postcode', 'city', 'country']),
                ]),
            ])
            ->filters([
                TrashedFilter::make()
                    ->label(__('client.table.filter.archived.label'))
                    ->placeholder(__('client.table.filter.archived.without'))
                    ->trueLabel(__('client.table.filter.archived.with'))
                    ->falseLabel(__('client.table.filter.archived.only')),
                Filter::make('appointed_at')
                    ->form([
                        DatePicker::make('appointed_at')
                            ->native(false)
                            ->label(__('client.table.sort.appointed_at'))
                            ->closeOnDateSelection()
                            ->suffixIcon('heroicon-m-calendar'),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['appointed_at']) {
                            return null;
                        }

                        return __('client.table.sort.appointed_at').': '.Utils::formatDate(Carbon::parse($data['appointed_at']));
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['appointed_at'],
                                function (Builder $query, $date): Builder {
                                    $users = Client::join(
                                        'appointments',
                                        'appointments.client_id',
                                        '=',
                                        'clients.id',
                                    )
                                        ->whereDate('appointed_at', '=', $date)
                                        ->select('clients.id');

                                    return $query->whereIn('id', $users->pluck('id'));
                                },
                            );
                    }),
                Filter::make('not_appointed_since')
                    ->form([
                        DatePicker::make('not_appointed_since')
                            ->native(false)
                            ->label(__('client.table.sort.not_appointed_since'))
                            ->closeOnDateSelection()
                            ->suffixIcon('heroicon-m-calendar'),
                    ])
                    ->indicateUsing(function (array $data): ?string {
                        if (! $data['not_appointed_since']) {
                            return null;
                        }

                        return __('client.table.sort.not_appointed_since').': '.Utils::formatDate(Carbon::parse($data['not_appointed_since']));
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['not_appointed_since'],
                                function (Builder $query, $date): Builder {
                                    $users = Client::all()->filter(
                                        fn ($client) => $client->lastAppointment <= Carbon::parse($date)
                                    );

                                    return $query->whereIn('id', $users->pluck('id'));
                                },
                            );
                    }),
            ])
            ->defaultSort('lastAppointment')
            ->persistSortInSession()
            ->persistFiltersInSession()
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    static::actionArchive(DeleteBulkAction::make()),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    static::actionEdit(TableAction::make('edit_client')),
                    static::actionArchive(DeleteAction::make()),
                    RestoreAction::make()
                        ->color('warning'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            AppointmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClients::route('/'),
            'create' => Pages\CreateClient::route('/create'),
            'view' => Pages\ViewClient::route('/{record}'),
            'edit' => Pages\EditClient::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function actionArchive(
        Action|TableAction|BulkAction $action,
    ): Action|TableAction|BulkAction {
        $description_1 = __('client.view.action.delete.modal.description_1');
        $description_2 = __('client.view.action.delete.modal.description_2');
        $description_3 = __('client.view.action.delete.modal.description_3');

        $action
            ->successNotificationTitle(__('client.view.action.delete.modal.notification.success'))
            ->modalDescription(
                new HtmlString(
                    <<<HTML
                    <div class="text-sm text-slate-400">
                        <div>$description_1</div>
                        <div class="my-2">$description_2</div>
                        <div>$description_3</div>
                    </div>
                    HTML
                )
            )
            ->modalIcon('heroicon-s-archive-box-arrow-down')
            ->icon('heroicon-s-archive-box-arrow-down')
            ->color('warning');

        if ($action instanceof BulkAction) {
            $action
                ->modalHeading(__('client.view.bulk_action.delete.modal.heading'))
                ->label(__('client.view.bulk_action.delete.label'));
        } else {
            $action->modalHeading(fn (Model $record) => __(
                'client.view.action.delete.modal.heading',
                ['fullname' => $record->full_name],
            ))
                ->label(__('client.view.action.delete.label'));
        }

        return $action;
    }

    public static function actionEdit(
        Action|TableAction $action,
    ): Action|TableAction {
        return $action
            ->label(__('client.form.edit.label'))
            ->icon('heroicon-m-pencil-square')
            ->modalFooterActionsAlignment(Alignment::End)
            ->modalSubmitActionLabel(__('client.form.edit.submit.label'))
            ->modalHeading(fn (Client $record) => __('client.form.edit.heading', ['name' => $record->fullname]))
            ->fillForm(fn (Client $record): array => [
                'name' => $record->name,
                'firstname' => $record->firstname,
                'primary_phone' => $record->primary_phone,
                'secondary_phone' => $record->secondary_phone,
                'email' => $record->email,
                'street' => $record->street,
                'postcode' => $record->postcode,
                'city' => $record->city,
                'country' => $record->country,
                'avatar_color' => $record->avatar_color,
                'notes' => $record->notes,
            ])
            ->authorize(fn (Client $record): bool => auth()->user()->can('update', $record))
            ->form(fn (Form $form) => ClientResource::form($form))
            ->action(function (array $data, Client $record): void {
                $record->name = $data['name'];
                $record->firstname = $data['firstname'];
                $record->primary_phone = $data['primary_phone'];
                $record->secondary_phone = $data['secondary_phone'];
                $record->email = $data['email'];
                $record->street = $data['street'];
                $record->postcode = $data['postcode'];
                $record->city = $data['city'];
                $record->country = $data['country'];
                $record->avatar_color = $data['avatar_color'];
                $record->notes = $data['notes'];
                $record->save();
            })
            ->slideOver();
    }

    public static function actionCreate(
        Action|TableAction $action,
    ): Action|TableAction {
        return $action
            ->label(__('client.form.create.label'))
            ->icon('heroicon-s-user-plus')
            ->modalFooterActionsAlignment(Alignment::End)
            ->modalSubmitActionLabel(__('client.form.create.submit.label'))
            ->modalHeading(__('client.form.create.heading'))
            ->authorize(fn (): bool => auth()->user()->can('create', Client::class))
            ->form(fn (Form $form) => ClientResource::form($form))
            ->action(function (array $data, Action $action): void {
                $record = new Client();
                $record->name = $data['name'];
                $record->firstname = $data['firstname'];
                $record->primary_phone = $data['primary_phone'];
                $record->secondary_phone = $data['secondary_phone'];
                $record->email = $data['email'];
                $record->street = $data['street'];
                $record->postcode = $data['postcode'];
                $record->city = $data['city'];
                $record->country = $data['country'];
                $record->avatar_color = $data['avatar_color'];
                $record->notes = $data['notes'];
                $record->save();
                $action->redirect(route('filament.admin.resources.clients.view', [
                    'record' => $record,
                ]));
            })
            ->slideOver();
    }
}
