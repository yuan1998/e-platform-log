<?php

namespace App\Admin\Renderable;

use App\Admin\Widgets\Charts\MyAjaxLine;
use App\Models\Product;
use App\Models\ProductLog;
use Carbon\Carbon;
use Dcat\Admin\Grid;
use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Widgets\Dropdown;

class ProductLogRenderable extends LazyRenderable
{
    public function render()
    {
        $id = $this->id;

        return Grid::make(ProductLog::query()->where('product_id', $id)->orderBy('date','desc'), function (Grid $grid) {
            $grid->scrollbarX();
            $grid->column('date','日期')->display(function ($val) {
                return Carbon::parse($val)->toDateString();
            });
            $grid->column('field','字段')->using(Product::LOG_FIELDS);
            $grid->column('action','行为');

            $grid->quickSearch([ 'field']);

            $grid->disableActions();
            $grid->disableCreateButton();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('field')->width(4);
            });
        });
    }
}
