<?php

namespace App\Admin\Controllers;

use App\Models\Category;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Tree;
use Dcat\Admin\Show;
use Dcat\Admin\Http\Controllers\AdminController;

class CategoryController extends AdminController
{

    public function index(Content $content)
    {
        return $content->header('品项分类')
            ->body(function (Row $row) {
                $tree = new Tree(new Category);

                $row->column(12, $tree);
            });
    }


    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new Category(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('parent_id');
            $grid->column('order');
            $grid->column('title');
            $grid->column('keyword');

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
        return Show::make($id, new Category(), function (Show $show) {
            $show->field('id');
            $show->field('parent_id');
            $show->field('order');
            $show->field('title');
            $show->field('keyword');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new Category(), function (Form $form) {
            $form->display('id');
            $form->select('parent_id')->options(Category::selectOptions())->default(0);
            $form->hidden('order');
            $form->text('title');
            $form->tags('keyword')->saving(function ($val) {
                return collect($val)->join(',');
            });
        });
    }
}
