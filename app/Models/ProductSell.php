<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSell extends Model
{
    use HasFactory;
    use HasDateTimeFormatter;

    public $timestamps = false;
    protected $fillable = [
        'product_id',
        'date',
        'sell',
    ];
}
