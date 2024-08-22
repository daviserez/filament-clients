<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.settings';

    public function getTitle(): string
    {
        return __('global.settings');
    }
}
