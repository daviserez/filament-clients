<?php

namespace App\Models;

use App\Models\Scopes\TeamScope;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'team_id',
        'name',
        'firstname',
        'primary_phone',
        'secondary_phone',
        'street',
        'postcode',
        'city',
        'country',
        'email',
        'avatar_color',
        'notes',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new TeamScope);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'team_id', 'team_id');
    }

    protected function fullName(): Attribute
    {
        $values = [];
        empty($this->firstname) ?: array_push($values, $this->firstname);
        empty($this->name) ?: array_push($values, $this->name);

        return Attribute::make(
            get: fn () => implode(' ', $values),
        );
    }

    protected function fullCity(): Attribute
    {
        $values = [];
        empty($this->postcode) ?: array_push($values, $this->postcode);
        empty($this->city) ?: array_push($values, $this->city);

        return Attribute::make(
            get: fn () => implode(' ', $values),
        );
    }

    protected function fulladress(): Attribute
    {
        $values = [];
        empty($this->street) ?: array_push($values, e($this->street));
        empty($this->fullCity) ?: array_push($values, e($this->fullCity));
        empty($this->country) ?: array_push($values, e($this->country));

        return Attribute::make(
            get: fn () => implode('<br>', $values),
        );
    }

    protected function fullphone(): Attribute
    {
        $values = [];
        empty($this->primary_phone) ?: array_push($values, e($this->primary_phone));
        empty($this->secondary_phone) ?: array_push($values, '<div class="text-slate-400">'.e($this->secondary_phone).'</div>');

        return Attribute::make(
            get: fn () => implode('<br>', $values),
        );
    }

    protected function lastAppointment(): Attribute
    {
        return Attribute::make(
            get: fn () => Appointment::where('client_id', $this->id)->latest('appointed_at')->first()->appointed_at ?? null,
        );
    }

    protected function totalAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->appointments()->join(
                'details_appointments',
                'appointments.id',
                '=',
                'details_appointments.appointment_id'
            )->sum('price'),
        );
    }

    protected function totalServiceAmount(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->appointments()->join(
                'details_appointments',
                'appointments.id',
                '=',
                'details_appointments.appointment_id'
            )->sum('service_price'),
        );
    }
}
