<?php

namespace App\Filament\Pages;

use App\Models\DetailsAppointment;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Livewire\Attributes\Validate;

enum ReportsType: int
{
    case Client = 0;
    case Date = 1;
    case Service = 2;
}

class Reports extends Page
{
    protected static string $view = 'filament.pages.reports';

    protected static ?string $navigationIcon = 'heroicon-s-chart-pie';

    protected static ?int $navigationSort = 3;

    #[Validate('required|gte:2000|lte:2100')]
    public int $year;

    #[Validate('required|gte:0|lte:12')]
    public int $month;

    public static function getNavigationLabel(): string
    {
        return __('global.reports.title');
    }

    public static function canAccess(): bool
    {
        // Only team managers can access reports.
        return auth()->user()->team_id === auth()->user()->id;
    }

    public function mount(): void
    {
        $this->year = date('Y');
        $this->month = date('n');
    }

    public function getTitle(): string|Htmlable
    {
        return static::getNavigationLabel();
    }

    public function getData(ReportsType $type): Collection
    {
        $this->validate();

        $details = DetailsAppointment::query()
            ->join('appointments', 'details_appointments.appointment_id', '=', 'appointments.id')
            ->join('clients', 'appointments.client_id', '=', 'clients.id')
            ->join('services', 'details_appointments.service_id', '=', 'services.id')
            ->selectRaw(<<<'SQL'
                firstname,
                clients.id AS client_id,
                clients.name AS client_name,
                services.id AS service_id,
                services.name AS service_name,
                DATE(appointed_at) AS appointed_at,
                details_appointments.price,
                details_appointments.service_price
            SQL)
            ->whereYear('appointed_at', $this->year)
            ->where('clients.team_id', auth()->user()->id)
            ->where('services.team_id', auth()->user()->id);

        if ($this->month > 0) {
            $details->whereMonth('appointed_at', $this->month);
        }

        $data = match ($type) {
            ReportsType::Client => $details
                ->orderBy('firstname')
                ->orderBy('clients.name')
                ->orderByDesc('price')
                ->get()
                ->groupBy('client_id'),
            ReportsType::Date => $details
                ->orderBy('appointed_at')
                ->orderBy('firstname')
                ->orderBy('clients.name')
                ->orderByDesc('price')
                ->get()
                ->groupBy('appointed_at')
                ->map(function ($group) {
                    return $group->groupBy('client_id');
                }),
            ReportsType::Service => $details
                ->orderBy('service_name')
                ->orderBy('firstname')
                ->orderBy('clients.name')
                ->get()
                ->groupBy('service_id'),
        };

        return $data;
    }
}
