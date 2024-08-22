<?php

namespace App\Providers;

use Filament\Forms\Components\DateTimePicker;
use Filament\Infolists\Infolist;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $separator = __('global.settings.date.separator');
        Model::unguard();
        DateTimePicker::$defaultDateDisplayFormat = config('app.default_format');
        DateTimePicker::$defaultDateTimeDisplayFormat = config('app.default_format')." \\$separator H:i";
        Table::$defaultDateDisplayFormat = config('app.default_format');
        Infolist::$defaultDateDisplayFormat = config('app.default_format');
    }
}
