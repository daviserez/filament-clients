<?php

namespace App\Helpers;

use DateTime;
use Illuminate\Support\Carbon;

class Utils
{
    public static function formatDate(string|DateTime $date): string
    {
        return Carbon::parse(
            $date,
        )->translatedFormat(config('app.default_format'));
    }

    public static function formatHours(mixed $date, ?string $timezone = null): ?string
    {
        if (is_null($timezone)) {
            $timezone = config('app.timezone');
        }
        
        $date = Carbon::parse($date);

        if ($date > $date->clone()->startOfDay()) {
            return $date->setTimezone($timezone)->translatedFormat('H:i');
        }

        return null;
    }

    public static function castMoney(mixed $value): float
    {
        return round(floatval($value) / 100, precision: 0);
    }

    public static function formatNumber(
        mixed $value,
        bool $castMoney = true,
        bool $addCurrency = false,
    ): mixed {
        $formattedNumber = number_format(
            $castMoney ? Utils::castMoney($value) : $value,
            thousands_separator: '\'',
            decimal_separator: '.',
        );
        $currency = config('app.currency');

        return $addCurrency ? "$formattedNumber $currency" : $formattedNumber;
    }
}
