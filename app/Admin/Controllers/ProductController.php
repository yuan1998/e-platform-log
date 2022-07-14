<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\MultipleProductLineChart;
use App\Admin\Renderable\LineChart;
use App\Admin\Widgets\Charts\MyAjaxLine;
use App\Admin\Widgets\Charts\MyLine;
use App\Models\HospitalInfo;
use App\Models\Product;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Widgets\Dropdown;
use Dcat\Admin\Widgets\Modal;

class ProductController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(Product::with(['hospital'])->orderBy('updated_at', 'desc'), function (Grid $grid) {
            $grid->scrollbarX();

//            $grid->batchActions([
//                new MultipleProductLineChart()
//            ]);

            $grid->column('id')->display(function ($val) {

                return Modal::make()
                    ->lg()
                    ->delay(300) // loading 效果延迟时间设置长一些，否则图表可能显示不出来
//                    ->($dropdown)
                    ->title($this->name)
                    ->body(LineChart::make(['id' => $val]))
                    ->button('<button class="btn btn-white"><i class="feather icon-bar-chart-2"></i></button>');
            });
//            $grid->column('origin_id');
            $grid->column('name');
            $grid->column('price');
            $grid->column('online_price');
            $grid->column('sell');
            $grid->column('status')->using(Product::STATUS);
            $grid->column('hospital.name', '医院名称');
            $grid->column('platform_type')->using(HospitalInfo::PLATFORM_LIST);
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new Product(), function (Show $show) {
            $show->field('id');
            $show->field('origin_id');
            $show->field('name');
            $show->field('hospital_id');
            $show->field('platform_type');
            $show->field('price');
            $show->field('online_price');
            $show->field('sell');
            $show->field('status');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Product(), function (Form $form) {
            $form->display('id');
            $form->text('origin_id');
            $form->text('name');
            $form->text('hospital_id');
            $form->text('platform_type');
            $form->text('price');
            $form->text('online_price');
            $form->text('sell');
            $form->text('status');

            $form->display('created_at');
            $form->display('updated_at');
        });
    }
}
