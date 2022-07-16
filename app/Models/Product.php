<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Product extends Model
{
    use HasFactory;
    use HasDateTimeFormatter;

    public const CREATED_AT = null;
    protected $fillable = [
        "origin_id",
        "name",
        "hospital_id",
        "platform_type",
        "price",
        "online_price",
        "status",
        "sell",
        "star",
        "created_at",
    ];

    const ONLINE_STATUS = 0;
    const OFFLINE_STATUS = 1;

    const STATUS = [
        self::ONLINE_STATUS => '上架',
        self::OFFLINE_STATUS => '下架',
    ];

    const LOG_FIELDS = [
        'name' => '名称',
        'price' => '原价',
        'online_price' => '优惠价',
        'status' => '状态',
    ];

    public function hospital(): BelongsTo
    {
        return $this->belongsTo(HospitalInfo::class, 'hospital_id', 'id');
    }

    public function sellLog(): HasMany
    {
        return $this->hasMany(ProductSell::class, 'product_id', 'id');
    }

    public function changeLog(): HasMany
    {
        return $this->hasMany(ProductLog::class, 'product_id', 'id');
    }


}
