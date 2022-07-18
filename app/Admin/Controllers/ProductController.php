<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Tools\MultipleProductLineChart;
use App\Admin\Grid\Tools\PullHospitalTool;
use App\Admin\Renderable\LineChart;
use App\Admin\Widgets\Charts\MyAjaxLine;
use App\Admin\Widgets\Charts\MyLine;
use App\Models\Category;
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
        Category::cacheKeyword();
        return Grid::make(Product::with(['hospital']), function (Grid $grid) {
            $grid->scrollbarX();
            $grid->tools(new PullHospitalTool());
            $grid->disableDeleteButton();
            $grid->disableQuickEditButton();
            $grid->disableViewButton();

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
            $grid->column('price')->sortable();
            $grid->column('online_price')->sortable();
            $grid->column('sell')->sortable();
            $grid->column('status')->using(Product::STATUS)
                ->filter(
                    Grid\Column\Filter\In::make(Product::STATUS)
                );
            $grid->column('star')->switch()->filter(
                Grid\Column\Filter\In::make([
                    0 => '不关注',
                    1 => '关注',
                ])
            );
            $grid->column('hospital.name', '医院名称');
            $grid->column('platform_type')->using(HospitalInfo::PLATFORM_LIST);
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();

            $grid->filter(function (Grid\Filter $filter) {
                $filter->like('name');
                $hospitals = HospitalInfo::query()
                    ->select(['name', 'id'])
                    ->where('enable', 1)
                    ->get()
                    ->pluck('name', 'id');
                $filter->equal('hospital_id')->select($hospitals);
                $filter->where('category_id', function ($query) {
                    $val = $this->input;
                    if ($val) {
                        $id = Category::allChildrenOfId($val)->pluck('id');
                        $query->whereIn('category_id', $id);
                    } else {
                        $query->where('category_id', 0);
                    }
                })->select(Category::selectOptions(null, '无分组'));

                $filter->equal('platform_type')->select(HospitalInfo::PLATFORM_LIST);

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
            $form->display('origin_id');
            $form->display('platform_type');
//            $form->text('hospital_id');
            $form->text('name');
            $form->decimal('price');
            $form->decimal('online_price');
            $form->number('sell');
            $form->select('status')->options(Product::STATUS);
            $form->select('category_id')->options(Category::selectOptions(null, '无分组'));
            $form->switch('star');

            $form->display('created_at');
            $form->display('updated_at');

            $form->submitted(function (Form $form) {
                $categoryId = $form->category_id;
                if ($categoryId) {
                    if (Category::query()->where('parent_id', $categoryId)->exists()) {
                        return $form->response()->error('只能选择最后一级的品类~');
                    }
                }

            });
        });
    }
}
