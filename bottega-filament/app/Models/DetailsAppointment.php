<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailsAppointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'service_id',
        'service_price',
        'price',
        'comment',
        'color',
    ];

    protected $casts = [
        'price' => MoneyCast::class,
        'service_price' => MoneyCast::class,
    ];

    public function scopeTeam(Builder $query): void
    {
        $query
            ->join('appointments', 'appointments.id', '=', 'details_appointments.appointment_id')
            ->join('clients', 'clients.id', '=', 'appointments.client_id')
            ->where('team_id', auth()->user()->team_id);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
