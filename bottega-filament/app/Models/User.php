<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    const DEFAULT_OPTIONS = [
        'dashboard' => [
            'show_latest_clients' => true,
            'show_next_clients' => true,
            'show_sales_figures' => true,
            'show_appointments_number' => true,
            'show_medails' => true,
            'show_tierlist_clients' => true,
            'hours_appointments_stay_in_next' => 2,
        ],
        'team' => [
            'name' => null,
            'members' => [],
        ],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'team_id',
        'name',
        'email',
        'password',
        'options',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'options' => 'array',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'team_id', 'team_id');
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'team_id', 'team_id');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function isTeamManager(): bool
    {
        return $this->team_id === $this->id;
    }

    public function getTeamName(): ?string
    {
        return $this->getOption('team', 'name') ?? __('global.settings.team.name.default', ['name' => $this->name]);
    }

    public function getOption(...$keys): mixed
    {
        $option = $this->options[$keys[0]] ?? null;
        $default = static::DEFAULT_OPTIONS[$keys[0]];

        unset($keys[0]);

        foreach ($keys as $key) {
            $option = $option[$key] ?? null;
            $default = $default[$key];
        }

        return $option ?? $default;
    }

    public function getTeamsAsMember(): array
    {
        // TODO refactor this with teams table in database.
        $teams = [];
        foreach (User::all() as $user) {
            if (in_array($this->id, $user->getOption('team', 'members'))) {
                $teams[$user->team_id] = $user->getTeamName();
            }
        }

        return $teams;
    }
}
