<?php

namespace App\Filament\Widgets;

use App\Helpers\Utils;
use App\Models\Appointment;
use App\Models\Client;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class ClientTierList extends BaseWidget
{
    protected static ?int $sort = 6;

    protected int|string|array $columnSpan = 2;

    public static function canView(): bool
    {
        return auth()->user()->getOption('dashboard', 'show_tierlist_clients') && auth()->user()->isTeamManager();
    }

    public function table(Table $table): Table
    {
        return $table
            // "All" bug because it try to count(*) and order by on a renamed
            // column (that thus do not exists in the query because there are only the count(*)).
            ->paginated([10, 25, 50, 100])
            ->heading(__('client.widget.tier_list.title'))
            ->description(__('client.widget.tier_list.description'))
            ->persistFiltersInSession()
            ->searchPlaceholder(__('client.widget.tier_list.search.placeholder'))
            ->persistSortInSession()
            ->defaultSort('price', 'desc')
            ->query(
                Client::query()
                    ->join(
                        'appointments',
                        'appointments.client_id',
                        '=',
                        'clients.id'
                    )->join(
                        'details_appointments',
                        'appointments.id',
                        '=',
                        'details_appointments.appointment_id'
                    )
                    ->groupByRaw('client_id, name, firstname')
                    ->selectRaw('
                        client_id AS id,
                        name,
                        firstname,
                        sum(price) AS price,
                        sum(service_price) AS total_price,
                        sum(service_price) - sum(price) AS reduction,
                        count(distinct appointments.id) AS nb_appointments'
                    )
            )
            ->columns([
                TextColumn::make('fullname')
                    ->label(__('client.widget.fullname'))
                    ->searchable(['name', 'firstname'])
                    ->sortable(['firstname', 'name']),
                TextColumn::make('price')
                    ->label(__('client.widget.tier_list.price'))
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(
                        fn (string $state): string => Utils::formatNumber($state, addCurrency: true)
                    ),
                TextColumn::make('total_price')
                    ->label(__('client.widget.tier_list.service_price'))
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(
                        fn (string $state): string => Utils::formatNumber($state, addCurrency: true)
                    ),
                TextColumn::make('reduction')
                    ->label(__('client.widget.tier_list.reduction'))
                    ->sortable()
                    ->toggleable()
                    ->formatStateUsing(
                        fn (string $state): string => Utils::formatNumber($state, addCurrency: true)
                    ),
                TextColumn::make('nb_appointments')
                    ->label(__('client.widget.tier_list.nb_appointments'))
                    ->toggleable()
                    ->sortable(),
            ])
            ->recordUrl(
                fn (Client $record): string => route('filament.admin.resources.clients.view', $record)
            )
            ->filters([
                SelectFilter::make('filter_by_year')
                    ->options(
                        fn (): array => Appointment::query()
                            ->team()
                            ->selectRaw('year(appointed_at) AS year')
                            ->groupByRaw('year(appointed_at)')
                            ->orderByDesc('appointed_at')
                            ->pluck('year', 'year')
                            ->all()
                    )
                    ->label(__('client.widget.tier_list.filter.year.label'))
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['value']) {
                            return $query->whereYear('appointed_at', $data['value']);
                        }

                        return $query;
                    }),
            ]);
    }
}
