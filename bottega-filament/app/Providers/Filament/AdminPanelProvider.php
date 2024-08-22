<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Settings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->profile(EditProfile::class)
            ->requiresEmailVerification(true)
            ->userMenuItems([
                'profile' => MenuItem::make()
                    ->label(fn () => auth()->user()->name ?? 'Profil')
                    ->color('primary'),
            ])
            ->login()
            ->passwordReset()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label(__('global.settings'))
                    ->url(fn (): string => Settings::getUrl())
                    ->icon('heroicon-s-cog-6-tooth'),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->brandName('La Bottega')
            ->brandLogo(asset('images/logo.svg'))
            ->darkModeBrandLogo(asset('images/logo-dark.svg'))
            ->brandLogoHeight('2.5rem')
            ->authMiddleware([
                Authenticate::class,
            ])
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->topNavigation()
            ->favicon(asset('images/favicon.png'))
            ->viteTheme('resources/css/filament/admin/theme.css');

        if (config('app.auth_can_register')) {
            $panel->registration()->emailVerification();
        }

        return $panel;
    }
}
