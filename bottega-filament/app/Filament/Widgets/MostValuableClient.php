<?php

namespace App\Filament\Widgets;

use App\Helpers\Utils;
use App\Models\Client;
use DateTime;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class MostValuableClient extends BaseWidget
{
    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        return auth()->user()->getOption('dashboard', 'show_medails') && auth()->user()->isTeamManager();
    }

    protected function getStats(): array
    {
        return [
            $this->getMvpStat(__('chart.stats.mvp.title')),
            $this->getMvpStat(__('chart.stats.mvp.year.title'), year: true),
            $this->getLoyalStat(__('chart.stats.fidele.title')),
            $this->getLoyalStat(__('chart.stats.fidele.year.title'), year: true),
        ];
    }

    private function getMvpStat(string $title, bool $year = false): ?Stat
    {
        $result = DB::select(
            <<<'SQL'
                SELECT
                    client_id,
                    SUM(details_appointments.price) AS total_amount
                FROM
                    `appointments`
                    INNER JOIN `clients` ON `clients`.`id` = `appointments`.`client_id`
                    INNER JOIN `details_appointments` ON `appointments`.`id` = `details_appointments`.`appointment_id`
                WHERE TRUE
                    AND `team_id` = :team_id
                    AND `appointed_at` >= :date
                GROUP BY
                    `client_id`
                ORDER BY
                    `total_amount` DESC
                LIMIT 1
            SQL,
            [
                'team_id' => auth()->user()->team_id,
                'date' => $year ? now()->startOfYear() : (new DateTime())->setTimestamp(0),
            ]
        )[0] ?? null;

        $client = Client::withTrashed()->find($result?->client_id);

        if ($client) {

            return Stat::make($title, $client->fullName)
                ->description(
                    Utils::formatNumber(
                        $result->total_amount,
                        addCurrency: true
                    )
                )
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'href' => "admin/clients/$client->id",
                    'wire:navigate' => '',
                ]);
        }

        return null;
    }

    private function getLoyalStat(string $title, bool $year = false): ?Stat
    {
        $result = DB::select(
            <<<'SQL'
                SELECT
                    client_id,
                    COUNT(client_id) AS nb_appointments
                FROM
                    `appointments`
                    INNER JOIN `clients` ON `clients`.`id` = `appointments`.`client_id`
                WHERE TRUE
                    AND `team_id` = :team_id
                    AND `appointed_at` >= :date
                GROUP BY
                    `client_id`
                ORDER BY
                    `nb_appointments` DESC
                LIMIT 1
            SQL,
            [
                'team_id' => auth()->user()->team_id,
                'date' => $year ? now()->startOfYear() : (new DateTime())->setTimestamp(0),
            ]
        )[0] ?? null;

        $client = Client::withTrashed()->find($result?->client_id);

        if ($client) {
            return Stat::make($title, $client->fullName)
                ->description(
                    Utils::formatNumber(
                        $result->nb_appointments,
                        castMoney: false,
                    ).' '.__('client.view.appointments.plural')
                )
                ->extraAttributes([
                    'class' => 'cursor-pointer',
                    'href' => "admin/clients/$client->id",
                    'wire:navigate' => '',
                ]);
        }

        return null;
    }
}
