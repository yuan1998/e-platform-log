<?php

namespace App\Admin\Grid\Tools;

use App\Admin\Forms\PullProductForm;
use App\Models\HospitalInfo;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Http\Request;

class PullHospitalTool extends AbstractTool
{

    /**
     * 按钮样式定义，默认 btn btn-white waves-effect
     *
     * @var string
     */
    protected $style = '';

    public function render()
    {
        return Modal::make()
            ->lg()
            ->title('获取本日数据')
            ->body(PullProductForm::make())
            ->button('<button class="btn btn-white waves-effect">获取本日数据</button>');

    }
}
