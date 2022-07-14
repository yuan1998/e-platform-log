<?php

namespace App\Admin\Widgets\Charts;

use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MyAjaxLine extends MyLine
{
    protected $id;

    // 这里的参数一定要设置默认值
    public function __construct($id = null)
    {
        parent::__construct();

        $this->id = $id;
    }

    /**
     * 处理请求
     * 如果你的图表类中包含此方法，则可以通过此方法处理前端通过ajax提交的获取图表数据的请求
     *
     * @param Request $request
     * @return mixed|void
     */
    public function handle(Request $request)
    {
        // 获取 parameters 方法设置的自定义参数
        $id = $request->get('id');
//        $id = [38, 72];
        $product = Product::with(['sellLog' => function ($query) {
            $today = Carbon::today();
            $end = $today->toDateTimeString();
            $start = $today->months(-1)->toDateTimeString();
            $query->whereBetween('date', [$start, $end]);
        }])
            ->whereIn('id', collect($id))
            ->get();


        $categories = [];
        $data = [];
        foreach ($product as $row) {
            $arr = [];
            foreach ($row->sellLog as $sell) {
                $date = Carbon::parse($sell['date'])->toDateString();
                $categories[] = $date;
                $arr[$date] = $sell['sell'];
            }

            $data[] = [
                'name' => $row['name'],
                'data' => $arr,
            ];
        }
        $categories = collect($categories)->sort()->unique()->values()->toArray();


        foreach ($data as &$item) {
            $arr = [];
            foreach ($categories as $category) {
                $arr[] = data_get($item, "data.$category", 0);
            }
            $item['data'] = $arr;
        }

//        $data = [
//            [
//                "name" => "Desktops",
//                'data' => [44, 55, 41, 64, 22, 43, 21]
//            ],
//            [
//                "name" => "Mobiles",
//                'data' => [53, 32, 33, 52, 13, 44, 11]
//            ]
//        ];
//        $categories = [2001, 2002, 2003, 2004, 2005, 2006, 2007];

        $this->withData($data);
        $this->withCategories($categories);
    }

    /**
     * 这里返回需要异步传递到 handler 方法的参数
     *
     * @return array
     */
    public function parameters(): array
    {
        return [
            'id' => $this->id,
        ];
    }

    /**
     * 这里覆写父类的方法，不再查询数据
     */
    protected function buildData()
    {
    }
}
