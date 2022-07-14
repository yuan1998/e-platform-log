<?php

namespace App\Models;

use App\Clients\XinYanClient;
use Carbon\Carbon;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class HospitalInfo extends Model
{
    use HasFactory;
    use HasDateTimeFormatter;

    const PLATFORM_LIST = [
        0 => '新氧',
    ];

    const PLATFORM_CLIENT = [
        0 => XinYanClient::class,
    ];

    protected $fillable = [
        'name',
        'url',
        'origin_id',
        'platform_type',
        'enable',
    ];

    /**
     * @throws Exception
     */
    public function client(): XinYanClient
    {
        $klass = data_get(self::PLATFORM_CLIENT, $this->platform_type);
        if (!$klass)
            throw new Exception("Oops! Can not find client Class, pls Check.");

        return new $klass($this);
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function getProducts($logSell = true)
    {
        $rows = $this->client()->search();
        $yesterday = Carbon::yesterday()->toDateTime();

        $ids = [];
        foreach ($rows as $row) {
            $p = Product::updateOrCreate(Arr::only($row, ['origin_id', 'hospital_id']), Arr::except($row, ['origin_id', 'hospital_id']));
            $id = $p->id;
            $ids[] = $id;
            if ($logSell) {
                ProductSell::updateOrCreate([
                    'product_id' => $id,
                    'date' => $yesterday,
                ], ['sell' => $row['sell']]);
            }
        }

        Product::query()
            ->where('hospital_id', $this->id)
            ->whereNotIn('id', $ids)
            ->update([
                'status' => Product::OFFLINE_STATUS
            ]);

    }


}
