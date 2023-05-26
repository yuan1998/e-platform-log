<?php

namespace App\Admin\Forms;

use Dcat\Admin\Widgets\Form;

class SettingForm extends Form
{
    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {

        admin_setting($input);
        return $this
            ->response()
            ->success('配置保存成功!')
            ->refresh();
    }

    /**
     * Build a form here.
     */
    public function form()
    {
        $this->divider('自动拉取部分');
        $this->switch('DA_ZHONG_ENABLE', '开启大众数据');

        $this->disableResetButton();
    }

    /**
     * The data of the form.
     *
     * @return array
     */
    public function default()
    {
        return admin_setting()->toArray();
    }
}
