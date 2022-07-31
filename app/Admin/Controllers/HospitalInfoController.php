<?php

namespace App\Admin\Controllers;

use App\Models\HospitalInfo;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class HospitalInfoController extends AdminController
{
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new HospitalInfo(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('name');
//            $grid->column('url');
            $grid->column('origin_id');
            $grid->column('platform_type')->using(HospitalInfo::PLATFORM_LIST);
            $grid->column('enable')->switch();
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
        return Show::make($id, new HospitalInfo(), function (Show $show) {
            $show->field('id');
            $show->field('name');
            $show->field('url');
            $show->field('origin_id');
            $show->field('platform_type');
            $show->field('enable');
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
        return Form::make(new HospitalInfo(), function (Form $form) {
            $form->display('id');
            $form->text('name');

            $form->switch('enable','新氧开关')->default(1);
            $form->text('url','新氧链接');
            $form->hidden('origin_id');

            $form->switch('dz_enable','大众开关')->default(1);
            $form->text('dz_url','大众链接');
            $form->hidden('dz_origin_id');

            $form->display('created_at');
            $form->display('updated_at');

            $form->submitted(function (Form $form) {
                if($form->enable) {
                    $url = $form->url;
                    preg_match('/(\d+)/', $url, $matches);
                    $id = data_get($matches, 1);
                    if (!$id)
                        return $form->response()->error('出错了,无法匹配到原始ID~');

                    $form->input('origin_id', $id);
                }
                if($form->dz_enable) {
                    $url = $form->dz_url;
                    preg_match('/\/(\w+)$/', $url, $matches);
                    $id = data_get($matches, 1);

                    if (!$id)
                        return $form->response()->error('出错了,无法匹配到原始ID~');

                    $form->input('dz_origin_id', $id);
                }


            });
        });
    }
}
