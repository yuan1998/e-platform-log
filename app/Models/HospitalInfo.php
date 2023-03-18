<?php

namespace App\Models;

use App\Clients\DaZhongClient;
use App\Clients\XinYanClient;
use App\Jobs\ClientProductPullJob;
use Carbon\Carbon;
use Closure;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

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

    public function scopeTypeQuery(Builder $query, $type)
    {
        foreach ($type as $v) {
            switch ($v) {
                case "0":
                    $query->where('enable', true);
                    break;
                case "1":
                    $query->where('dz_enable', true);
                    break;
            }
        }
    }


    public static function pullAll(Closure $callback = null, $queue = true, $date = null)
    {
        $query = HospitalInfo::query();
        if ($callback) {
            call_user_func($callback, $query);
        }

        $date = $date ?? Carbon::today()->toDateString();
        $hospital = $query->get();
        foreach ($hospital as $index => $item) {
            $delay = now()->addMinutes($index * 1);
            Log::debug("延迟" , [
                $item['name'],
                $delay->toDateTimeString(),
            ]);
            if ($item['enable'] && $item['origin_id']) {
                if ($queue)
                    ClientProductPullJob::dispatch($item, $date, self::XINYAN_ID)->onQueue('client')->delay($delay);
                else
                    $item->getProducts(self::XINYAN_ID, $date);
            }

            if ($item['dz_enable'] && $item['dz_origin_id']) {
                if ($queue)
                    ClientProductPullJob::dispatch($item, $date, self::DAZHONG_ID)->onQueue('client')->delay($delay);
                else {
                    $item->getProducts(self::DAZHONG_ID, $date);
                }
            }
        }

    }

}
