<?php

namespace App\Admin\Renderable;

use App\Admin\Widgets\Charts\MyAjaxLine;
use App\Models\ProductLog;
use Dcat\Admin\Grid;
use Dcat\Admin\Support\LazyRenderable;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Widgets\Dropdown;

class ProductLogRenderable extends LazyRenderable
{
    public function render()
    {
        $id = $this->id;

        return Grid::make(ProductLog::query()->where('product_id',$id), function (Grid $grid) {

            $grid->column('date');
            $grid->column('name');
            $grid->column('field');
            $grid->column('updated_at');

            $grid->quickSearch(['id', 'username', 'name']);

            $grid->paginate(10);
            $grid->disableActions();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('username')->width(4);
                $filter->like('name')->width(4);
            });
        });
    }
}
