<?php

namespace App\Admin\Forms;

use App\Models\Category;
use App\Models\HospitalInfo;
use App\Models\Product;
use Carbon\Carbon;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;

class PullProductForm extends Form implements LazyRenderable
{
    use LazyWidget;

    // 处理请求
    public function handle(array $input)
    {
        $queue = $input['queue'] === '1';
        $date = $input['yesterday'] ? Carbon::yesterday()->toDateString() : today()->toDateString();
        HospitalInfo::pullAll(function (\Illuminate\Database\Eloquent\Builder $query) use ($input) {
            if ($type = $input['platform_type']) {
                $query->typeQuery($type);
            }

            if ($id = $input['id']) {
                $query->whereIn('id', $id);
            }
        }, $queue,$date);


        return $this->response()->success($queue ? '开始队列获取,请耐心等待运行结束~' : '获取数据完成~')->refresh();
    }

    public function form()
    {
        // 获取外部传递参数
        $this->switch('yesterday' , '昨天')->default(false);

        $this->multipleSelect('platform_type', '平台列表')
            ->options(HospitalInfo::PLATFORM_LIST)
            ->placeholder('选择拉取的平台(不选择默认全部');

        $hospitals = HospitalInfo::query()
            ->select(['name', 'id'])
            ->get()
            ->pluck('name', 'id');
        $this->multipleSelect('id', '医院列表')->options($hospitals)
            ->placeholder('选择拉取的医院(不选择默认全部');

        $this->radio('queue', '拉取方式')
            ->options([
                1 => '队列运行',
                2 => '同步运行',
            ])
            ->default(1);

    }


    public function actionScript(): string
    {
        return <<<'JS'
function (data, target, action) {
    console.log(data);
    console.log(target);
    console.log(action);
 }
JS;
    }

    // 返回表单数据，如不需要可以删除此方法
    public function default()
    {


        return [

        ];
    }
}
