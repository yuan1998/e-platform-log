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

        return MyAjaxLine::make($id);
    }
}
