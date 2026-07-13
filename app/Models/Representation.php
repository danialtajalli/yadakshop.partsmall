<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Representation extends Model
{
    use BelongsToCity;
    use Searchable;

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

    public function toSearchableArray(): array
    {
        $this->loadMissing(['company', 'city.state']);

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'responsible_person_name' => $this->responsible_person_name,
            'work_fields' => strip_tags((string) $this->work_fields),
            'service_type' => $this->service_type,
            'description' => strip_tags((string) $this->description),
            'address' => $this->address,
            'company_name' => $this->company?->name,
            'state_name' => $this->city?->state?->name,
            'city_name' => $this->city?->name,
        ];
    }

    public function searchableAs(): string
    {
        return 'representations';
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function scopeVisibleUnderProduct(Builder $query): void
    {
        $query->where('show_under_product', true);
    }
}
