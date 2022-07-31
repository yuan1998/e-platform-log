<?php

namespace App\Admin\Grid\Tools;

use App\Models\HospitalInfo;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Illuminate\Http\Request;

class PullHospitalTool extends AbstractTool
{
    /**
     * 按钮样式定义，默认 btn btn-white waves-effect
     *
     * @var string
     */
    protected $style = 'btn btn-white waves-effect';


    /**
     * 按钮文本
     *
     * @return string|void
     */
    public function title()
    {
        return '获取本日数据';
    }

    /**
     *  确认弹窗，如果不需要则返回空即可
     *
     * @return array|string|void
     */
    public function confirm()
    {
        // 只显示标题
//        return '您确定要发送新的提醒消息吗？';

        // 显示标题和内容
        return ['确定要获取本日商品数据吗？'];
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


    /**
     * 处理请求
     * 如果你的类中包含了此方法，则点击按钮后会自动向后端发起ajax请求，并且会通过此方法处理请求逻辑
     *
     * @param Request $request
     */
    public function handle(Request $request)
    {
        // 你的代码逻辑
        HospitalInfo::pullAll();
        return $this->response()->success('开始队列获取,请耐心等待运行结束~')->refresh();
    }

    /**
     * 设置请求参数
     *
     * @return array|void
     */
    public function parameters()
    {
        return [

        ];
    }
}
