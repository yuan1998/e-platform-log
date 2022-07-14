<?php

namespace App\Admin\Renderable;

use App\Admin\Widgets\Charts\MyAjaxLine;
use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Widgets\Dropdown;

class LineChart extends LazyRenderable
{
    public function render()
    {
        $id = $this->id;
//        $menu = [
//            '7' => '最近7天',
//            '28' => '最近28天',
//            '30' => '最近30天',
//        ];
//        $dropdown = Dropdown::make($menu)
//            ->button(current($menu))
//            ->click()
//            ->map(function ($v, $k) {
//                // 此处设置的 data-xxx 属性会作为post数据发送到后端api
//                return "<a class='switch-bar-{$this->id}' data-option='{$k}'>{$v}</a>";
//            });
//
//        $bar = MyAjaxLine::make($id)
//            ->click(".switch-bar-{$this->id}"); // 设置图表点击菜单则重新发起请求，且被点击的目标元素上的 data-xxx 属性会被作为post数据发送到后端API
//
//        $box = Box::make('我的图表2', $bar)
//            ->tool($dropdown); // 设置下拉菜单按钮

        return MyAjaxLine::make($id);
    }
}
