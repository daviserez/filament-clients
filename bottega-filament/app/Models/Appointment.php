<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'appointed_at',
    ];

    protected $casts = [
        'appointed_at' => 'datetime',
    ];

    public function scopeTeam(Builder $query): void
    {
        $query
            ->join('clients', 'clients.id', '=', 'appointments.client_id')
            ->where('team_id', auth()->user()->team_id);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(DetailsAppointment::class)->orderBy('price', 'desc');
    }

    protected function totalAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->details->sum('price'),
        );
    }
}
