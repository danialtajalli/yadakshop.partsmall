<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Representation extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'responsible_person_name',
        'work_fields',
        'mobile',
        'telephone',
        'company_id',
        'service_type',
        'website',
        'website_name',
        'whatsapp',
        'whatsapp_phone',
        'telegram',
        'telegram_phone',
        'instagram',
        'state_id',
        'city_id',
        'address',
        'latitude',
        'longitude',
        'description',
        'logo',
        'nearby_railway',
        'nearby_bus',
        'nearby_railway_name',
        'nearby_bus_name',
        'nearby_railway_distance',
        'nearby_bus_distance',
        'show_under_product',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'nearby_railway_distance' => 'float',
            'nearby_bus_distance' => 'float',
            'show_under_product' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function scopeVisibleUnderProduct(Builder $query): void
    {
        $query->where('show_under_product', true);
    }
}
