<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartRepairCategory extends Model
{
    protected $table = 'part_repair_category';

    protected $fillable = [
        'part_id',
        'repair_category_id',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function repairCategory(): BelongsTo
    {
        return $this->belongsTo(RepairCategory::class);
    }
}
