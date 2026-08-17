<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'log_type',
        'occurred_at',
        'method',
        'url',
        'path',
        'route_name',
        'route_action',
        'status_code',
        'status_family',
        'is_reportable_status',
        'duration_ms',
        'user_id',
        'ip',
        'user_agent',
        'referer',
        'exception',
        'query',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'is_reportable_status' => 'boolean',
            'query' => 'array',
        ];
    }
}
