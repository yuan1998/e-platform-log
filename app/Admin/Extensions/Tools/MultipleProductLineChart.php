<?php

namespace App\Admin\Extensions\Tools;

use App\Admin\Renderable\LineChart;
use Dcat\Admin\Grid\BatchAction;
use Dcat\Admin\Widgets\Modal;

class MultipleProductLineChart extends BatchAction
{

    public function render()
    {
        return Modal::make()
            ->lg()
            ->delay(300)
            ->body('132')
            ->button($this->title);
    }

    /**
     * 设置动作发起请求前的回调函数，返回false可以中断请求.
     *
     * @return string
     */
    public function actionScript()
    {
        $warning = __('No data selected!');

        return <<<JS
function (data, target, action) {
    var key = {$this->getSelectedKeysScript()}

    if (key.length === 0) {
        Dcat.warning('{$warning}');
        return false;
    }

    // 设置主键为复选框选中的行ID数组
    action.options.key = key;
}
JS;
    }


}


