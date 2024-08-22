<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceResource\Pages;
use App\Models\Service;
use Filament\Actions\Action;
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
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-s-scissors';

    protected static ?string $recordTitleAttribute = 'name';

    protected static int $globalSearchResultsLimit = 10;

    protected static ?int $navigationSort = 2;

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $currency = config('app.currency');

        return [
            __('service.edit.price') => "{$record->price} $currency",
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
                TextInput::make('name')
                    ->label(__('service.edit.name'))
                    ->required()
                    ->maxLength(255)
                    ->autocomplete(false)
                    ->autofocus()
                    ->columnSpan(3),
                TextInput::make('price')
                    ->label(__('service.edit.price'))
                    ->required()
                    ->numeric()
                    ->autocomplete(false)
                    ->columnSpan(1),
            ])
            ->columns(4);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('service.table.name'))
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->icon(fn (Model $record) => $record->trashed() ? 'heroicon-s-archive-box' : null)
                    ->iconColor('warning')
                    ->iconPosition(IconPosition::After),
                TextColumn::make('price')
                    ->label(__('service.table.price'))
                    ->searchable()
                    ->sortable()
                    ->alignEnd()
                    ->suffix(' '.config('app.currency')),
                TextColumn::make('usage')
                    ->label(__('service.table.usage'))
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Make a subquery to retrieve the number of appointments for services.
                        $nbAppointments = DB::table('details_appointments')
                            ->select('service_id', DB::raw('COUNT(id) as nb_appointments'))
                            ->groupBy('service_id');

                        return $query
                            ->select('services.*')
                            ->leftJoinSub($nbAppointments, 'nb_appointments', function (JoinClause $join) {
                                $join->on('services.id', '=', 'nb_appointments.service_id');
                            })
                            ->orderBy('nb_appointments', $direction);
                    })
                    ->alignEnd()
                    ->toggleable(),
                TextColumn::make('lastUseBy')
                    ->label(__('service.table.last_use'))
                    ->html()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        // Make a subquery to retrieve the most recent appointment then sort by it.
                        $latestAppointments = DB::table('appointments')
                            ->join(
                                'details_appointments',
                                'appointments.id',
                                '=',
                                'details_appointments.appointment_id',
                            )
                            ->select('service_id', DB::raw('MAX(appointed_at) as last_appointment'))
                            ->groupBy('service_id');

                        return $query
                            ->select('services.*')
                            ->leftJoinSub($latestAppointments, 'latest_appointment', function (JoinClause $join) {
                                $join->on('services.id', '=', 'latest_appointment.service_id');
                            })
                            // Reverse the order because it seems more logical that ASC is the more recent appointment.
                            ->orderBy('last_appointment', $direction === 'asc' ? 'desc' : 'asc');
                    })
                    ->toggleable()
                    ->alignEnd(),
                TextColumn::make('created_at')
                    ->date()
                    ->label(__('service.table.created_at'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->filters([
                TrashedFilter::make()
                    ->label(__('service.table.filter.archived.label'))
                    ->placeholder(__('service.table.filter.archived.without'))
                    ->trueLabel(__('service.table.filter.archived.with'))
                    ->falseLabel(__('service.table.filter.archived.only')),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\EditAction::make()->modalFooterActionsAlignment(Alignment::End),
                    static::actionArchive(DeleteAction::make()),
                    Tables\Actions\ForceDeleteAction::make()->modalFooterActionsAlignment(Alignment::End),
                    Tables\Actions\RestoreAction::make()->modalFooterActionsAlignment(Alignment::End),
                ]),
            ], position: ActionsPosition::BeforeColumns)
            ->bulkActions([
                BulkActionGroup::make([
                    static::actionArchive(DeleteBulkAction::make()),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageServices::route('/'),
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
        $description_1 = __('service.view.action.delete.modal.description_1');
        $description_2 = __('service.view.action.delete.modal.description_2');
        $description_3 = __('service.view.action.delete.modal.description_3');

        $action
            ->label(__('service.view.action.delete.label'))
            ->modalHeading(fn (Model $record) => __(
                'service.view.action.delete.modal.heading',
                ['name' => $record->name],
            ))
            ->successNotificationTitle(__('service.view.action.delete.modal.notification.success'))
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
                ->label(__('service.view.bulk_action.delete.label'))
                ->modalHeading(__('service.view.bulk_action.delete.modal.heading'));
        } else {
            $action
                ->label(__('service.view.action.delete.label'))
                ->modalHeading(fn (Model $record) => __(
                    'service.view.action.delete.modal.heading',
                    ['name' => $record->name],
                ));
        }

        return $action;
    }
}
