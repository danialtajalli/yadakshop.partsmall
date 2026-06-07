<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $fillable = [
        'username',
        'topic',
        'message',
    ];

    public function phones(): HasMany
    {
        return $this->hasMany(Phone::class);
    }
}
