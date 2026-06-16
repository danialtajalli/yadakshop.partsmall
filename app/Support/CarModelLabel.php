<?php

namespace App\Support;

use App\Models\CarModel;

class CarModelLabel
{
    public static function display(CarModel $model): string
    {
        return is_numeric($model->slug) ? 'سال '.$model->name : $model->name;
    }
}
