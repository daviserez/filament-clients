<?php

namespace App\Filament\Resources\ClientResource\Widgets;

use App\Helpers\Utils;
use App\Models\Appointment;
use App\Models\Client;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Carbon;
use Livewire\Attributes\On;

class ClientOverview extends BaseWidget
{
    public ?Client $record = null;

    protected static ?string $pollingInterval = null;

    #[On('active-tab-changed')]
    public function loadStats()
    {
        $this->cachedStats = [
            $this->getGrandTotal(),
            $this->getMonthlyTrend(),
            $this->getTotalReduction(),
        ];
    }

    protected function getTotalReduction(): Stat
    {
        $totalFormatted = Utils::formatNumber(
            $this->record->totalServiceAmount - $this->record->totalAmount,
            addCurrency: true,
        );

        $endDate = $this->record->appointments()->latest('appointed_at')->first()->appointed_at ?? null;
        $data = [];

        if ($endDate) {

            $firstAppointment = $this->record->appointments()->oldest('appointed_at')->first()->appointed_at ?? null;
            $tenYearsSinceLastAppointment = $endDate?->clone()->subYears(10) ?? null;

            $startDate = max($firstAppointment, $tenYearsSinceLastAppointment);

            $trend = $this->getTrendQuery($startDate, $endDate)->perYear();

            $data = $trend->sum('COALESCE(service_price, price, 0) - price')->map(
                fn (TrendValue $value) => $value->aggregate
            )->toArray();
        }

        return Stat::make(__('client.widget.reduction.title'), $totalFormatted)
            ->chart($data)
            ->color('primary');
    }

    protected function getGrandTotal(): Stat
    {
        $totalFormatted = Utils::formatNumber(
            $this->record->totalAmount,
            addCurrency: true,
        );

        $endDate = $this->record->appointments()->latest('appointed_at')->first()->appointed_at ?? null;
        $data = [];

        if ($endDate) {

            $firstAppointment = $this->record->appointments()->oldest('appointed_at')->first()->appointed_at ?? null;
            $tenYearsSinceLastAppointment = $endDate?->clone()->subYears(10) ?? null;

            $startDate = max($firstAppointment, $tenYearsSinceLastAppointment);

            $trend = $this->getTrendQuery($startDate, $endDate)->perYear();

            $data = $trend->sum('price')->map(
                fn (TrendValue $value) => $value->aggregate
            )->toArray();
        }

        return Stat::make(__('client.widget.grand_total.title'), $totalFormatted)
            ->chart($data)
            ->color('primary');
    }

    protected function getMonthlyTrend(): Stat
    {
        $endDate = now();
        $startDate = $endDate?->clone()->subMonths(24) ?? null;

        $trend = $this->getTrendQuery($startDate, $endDate)->perMonth();

        $data = $trend->sum('price')->map(
            fn (TrendValue $value) => $value->aggregate
        )->toArray();

        if (count(array_filter(array_slice($data, -6, 6), fn ($value) => $value > 0)) < 3) {
            return Stat::make(__('client.widget.monthly.title'), null)
                ->description(__('client.widget.monthly.not_enough_values'));
        }

        $mean = array_sum(array_slice($data, -6, 6)) / 6;
        $totalMean = array_sum($data) / 24;
        $trendDirection = $totalMean > $mean ? 'down' : 'up';
        $color = $totalMean > $mean ? 'danger' : 'success';
        $diff = Utils::formatNumber($mean - $totalMean, addCurrency: true);

        $totalFormatted = Utils::formatNumber($mean, addCurrency: true);

        $icon = $trendDirection ? "heroicon-m-arrow-trending-$trendDirection" : null;

        return Stat::make(__('client.widget.monthly.title'), $totalFormatted)
            ->description("$diff")
            ->descriptionIcon($icon, IconPosition::Before)
            ->chart($data)
            ->color($color);
    }

    protected function getTrendQuery(Carbon $startDate, Carbon $endDate): Trend
    {
        return Trend::query(
            Appointment::team()
                ->where('client_id', $this->record->id)
                ->join(
                    'details_appointments',
                    'appointments.id',
                    '=',
                    'details_appointments.appointment_id'
                )
        )
            ->dateColumn('appointed_at')
            ->between(
                start: $startDate,
                end: $endDate,
            );
    }
}
