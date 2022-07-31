<?php

namespace App\Models;

use App\Clients\DaZhongClient;
use App\Clients\XinYanClient;
use App\Jobs\ClientProductPullJob;
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

    const XINYAN_ID = 0;
    const DAZHONG_ID = 1;

    const PLATFORM_LIST = [
        self::XINYAN_ID => '新氧',
        self::DAZHONG_ID => '大众',
    ];

    const PLATFORM_CLIENT = [
        self::XINYAN_ID => XinYanClient::class,
        self::DAZHONG_ID => DaZhongClient::class,
    ];

    protected $fillable = [
        'name',
        'url',
        'origin_id',
        'platform_type',
        'enable',
        'dz_origin_id',
        'dz_url',
        'dz_enable',
    ];

    /**
     * @throws Exception
     */
    public function getClient($type)
    {
        $klass = data_get(self::PLATFORM_CLIENT, $type);
        if (!$klass)
            throw new Exception("Oops! Can not find client Class, pls Check.");

        return new $klass($this);
    }

    /**
     * @throws Exception
     * @throws GuzzleException
     */
    public function getProducts($type = self::XINYAN_ID, $date = null, $logSell = true)
    {
        $rows = $this->getClient($type)->search();
        $yesterday = $date ?: Carbon::yesterday()->toDateTime();

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
            ->where('platform_type', $type)
            ->whereNotIn('id', $ids)
            ->update([
                'status' => Product::OFFLINE_STATUS
            ]);

    }


    public static function pullAll()
    {
        $hospital = HospitalInfo::all();
        $date = Carbon::today()->toDateString();
        foreach ($hospital as $item) {
            if ($item['enable'] && $item['origin_id'])
                ClientProductPullJob::dispatch($item, $date, self::XINYAN_ID)->onQueue('client');

            if ($item['dz_enable'] && $item['dz_origin_id'])
                ClientProductPullJob::dispatch($item, $date, self::DAZHONG_ID)->onQueue('client');
        }

    }

}
