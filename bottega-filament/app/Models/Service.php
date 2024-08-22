<?php

namespace App\Models;

use App\Casts\MoneyCast;
use App\Models\Scopes\TeamScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'price',
    ];

    protected $casts = [
        'price' => MoneyCast::class,
        'service_price' => MoneyCast::class,
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TeamScope);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailsAppointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_id', 'team_id');
    }

    protected function usage(): Attribute
    {
        return Attribute::make(
            get: fn () => DetailsAppointment::where('service_id', $this->id)->count('id') ?? 0,
        );
    }

    protected function lastUse(): Attribute
    {
        return Attribute::make(
            get: fn () => DetailsAppointment::query()
                ->where('service_id', $this->id)
                ->join(
                    'appointments',
                    'details_appointments.appointment_id',
                    '=',
                    'appointments.id'
                )
                ->latest('appointed_at')
                ->first() ?? null,
        );
    }

    protected function lastUseBy(): Attribute
    {
        if ($this->lastUse) {
            $lastUseSince = $this->lastUse->appointment->appointed_at?->diffForHumans();
            $lastUseBy = $this->lastUse->appointment->client()->withTrashed()->first()->fullName;
            $by = __('service.last_use_by');

            return Attribute::make(
                get: fn () => "$lastUseSince $by $lastUseBy",
            );
        }

        return Attribute::make(
            get: fn () => __('service.never_user'),
        );
    }
}
