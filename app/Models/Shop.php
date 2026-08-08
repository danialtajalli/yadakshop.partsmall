<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCity;
use App\Support\ShopQrCodeGenerator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\Searchable;

class Shop extends Model
{
    use BelongsToCity;
    use Searchable;

    protected $fillable = [
        'name',
        'secondary_name',
        'slug',
        'confirmed',
        'verified',
        'show_under_product',
        'description',
        'person_responsible_name',
        'person_responsible_email',
        'website_show',
        'order',
        'visited_count',
        'latitude',
        'longitude',
        'city_id',
        'address',
        'open_time',
        'close_time',
        'open_time_friday',
        'close_time_friday',
        'open_time_thursday',
        'close_time_thursday',
        'off',
    ];

    protected function casts(): array
    {
        return [
            'confirmed' => 'boolean',
            'verified' => 'boolean',
            'show_under_product' => 'boolean',
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'order' => 'integer',
            'visited_count' => 'integer',
            'off' => 'boolean',
        ];
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing(['partsCategories', 'companies', 'city.state']);

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'secondary_name' => $this->secondary_name,
            'slug' => $this->slug,
            'description' => strip_tags((string) $this->description),
            'person_responsible_name' => $this->person_responsible_name,
            'address' => $this->address,
            'state_name' => $this->city?->state?->name,
            'city_name' => $this->city?->name,
            'parts_categories' => $this->partsCategories->pluck('name')->all(),
            'companies' => $this->companies->pluck('name')->all(),
            'confirmed' => $this->confirmed,
        ];
    }

    public function searchableAs(): string
    {
        return 'shops';
    }

    protected static function booted(): void
    {
        static::creating(function (Shop $shop): void {
            if ($shop->order === null) {
                $shop->order = (static::max('order') ?? 0) + 1;
            }

            if ($shop->visited_count === null) {
                $shop->visited_count = random_int(2000, 2100);
            }
        });

        static::created(function (Shop $shop): void {
            try {
                ShopQrCodeGenerator::generate($shop);
            } catch (\Throwable $exception) {
                Log::warning('Shop QR generation failed after create.', [
                    'shop_id' => $shop->id,
                    'slug' => $shop->slug,
                    'error' => $exception->getMessage(),
                ]);
            }
        });
    }

    public function images(): HasMany
    {
        return $this->hasMany(Image::class, 'shop_id');
    }

    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function partsCategories(): BelongsToMany
    {
        return $this->belongsToMany(PartsCategory::class, 'parts_category_shop');
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class, 'part_shop');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_shops');
    }

    public function scopeVisibleUnderProduct(Builder $query): void
    {
        $query->where('show_under_product', true);
    }

    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('order')->orderBy('name')->orderBy('id');
    }

    public function scopeConfirmed(Builder $query): void
    {
        $query->where('confirmed', true);
    }
}
