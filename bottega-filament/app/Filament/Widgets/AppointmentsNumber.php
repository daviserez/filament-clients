<?php

namespace App\Filament\Widgets;

use App\Models\Appointment;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class AppointmentsNumber extends ChartWidget
{
    protected static ?int $sort = 4;

    public function getHeading(): ?string
    {
        return __('chart.appointment_number.label');
    }

    public static function canView(): bool
    {
        return auth()->user()->getOption('dashboard', 'show_appointments_number') && auth()->user()->isTeamManager();
    }

    protected function getData(): array
    {
        $generateData = function ($start, $end) {
            return Trend::query(
                Appointment::team()
            )
                ->dateColumn('appointed_at')
                ->between(
                    start: $start,
                    end: $end,
                );
        };

        $lastAppointmentDate = Appointment::team()
            ->latest('appointed_at')
            ->first()
            ->appointed_at ?? null;

        if (is_null($lastAppointmentDate)) {
            return [
                'datasets' => [
                    [
                        'label' => __('chart.appointment_number.label'),
                        'data' => [],
                    ],
                ],
                'labels' => [],
            ];
        }

        $firstAppointmentDate = Appointment::team()
            ->oldest('appointed_at')
            ->first()
            ->appointed_at;

        $data = (match ($this->filter) {
            'month_1' => $generateData(
                (clone $lastAppointmentDate)->subMonth(11),
                $lastAppointmentDate,
            )->perMonth(),
            'month_5' => $generateData(
                (clone $lastAppointmentDate)->subYears(4),
                $lastAppointmentDate,
            )->perMonth(),
            'month_all' => $generateData(
                $firstAppointmentDate,
                $lastAppointmentDate,
            )->perMonth(),
            'year_5' => $generateData(
                (clone $lastAppointmentDate)->subYears(4),
                $lastAppointmentDate,
            )->perYear(),
            'year_all' => $generateData(
                $firstAppointmentDate,
                $lastAppointmentDate,
            )->perYear(),
            default => $generateData(
                (clone $lastAppointmentDate)->subMonth(11),
                $lastAppointmentDate,
            )->perMonth(),
        })->count();

        return [
            'datasets' => [
                [
                    'label' => __('chart.appointment_number.label'),
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getFilters(): ?array
    {
        return [
            'month_1' => __('chart.appointment.filter.month_1'),
            'month_5' => __('chart.appointment.filter.month_5'),
            'month_all' => __('chart.appointment.filter.month_all'),
            'year_5' => __('chart.appointment.filter.year_5'),
            'year_all' => __('chart.appointment.filter.year_all'),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
