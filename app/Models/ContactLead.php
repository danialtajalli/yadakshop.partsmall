<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactLead extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    /** @var list<string> */
    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'message',
        'status',
        'pipeline',
        'didar_person_id',
        'didar_deal_id',
        'didar_product_id',
        'failure_reason',
    ];
}
