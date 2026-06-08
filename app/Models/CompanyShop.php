<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class CompanyShop extends Pivot
{
    public $incrementing = true;

    protected $table = 'company_shops';
}
