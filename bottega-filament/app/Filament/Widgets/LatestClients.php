<?php

namespace App\Filament\Widgets;

use App\Helpers\Utils;
use App\Models\Client;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestClients extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return auth()->user()->getOption('dashboard', 'show_latest_clients');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('client.widget.latest'))
            ->query(
                fn () => Client::join('appointments', 'clients.id', '=', 'appointments.client_id')
                    ->select('clients.*', 'appointments.appointed_at')
                    ->where(
                        fn ($query) => $query->where(
                            'appointments.appointed_at',
                            '<',
                            now()->subHours(
                                auth()->user()->getOption(
                                    'dashboard',
                                    'hours_appointments_stay_in_next',
                                )
                            )
                        )
                            ->orWhere(
                                fn ($query) => $query->whereDate(
                                    'appointments.appointed_at',
                                    '!=',
                                    now()->startOfDay()
                                )
                            )
                    )
                    ->orderBy('appointments.appointed_at', 'desc')
                    ->limit(5)
            )
            ->paginated([5, 10, 15])
            ->defaultPaginationPageOption(5)
            ->columns([
                TextColumn::make('fullname')->label(__('client.widget.fullname')),
                TextColumn::make('appointed_at')
                    ->date()
                    ->description(
                        fn (TextColumn $column, $state): ?string => Utils::formatHours($state, $column->getTimezone()),
                    )
                    ->label(__('client.view.appointments')),
            ])->recordUrl(fn (Client $record): string => route('filament.admin.resources.clients.view', $record));
    }
}
