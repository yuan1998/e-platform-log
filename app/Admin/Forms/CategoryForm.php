<?php

namespace App\Admin\Forms;

use App\Models\Category;
use App\Models\Product;
use Dcat\Admin\Widgets\Form;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Contracts\LazyRenderable;

class CategoryForm extends Form implements LazyRenderable
{
    use LazyWidget;

    // 处理请求
    public function handle(array $input)
    {
        // 获取外部传递参数
        $id = $this->payload['id'] ?? null;

        if (!$id)
            return $this->response()->error('错误,无法获取商品ID~');

        $categoryId = $input['category_id'] ?? 0;

        if ($categoryId) {
            if (Category::query()->where('parent_id', $categoryId)->exists()) {
                return $this->response()->error('只能选择最后一级的品类~');
            }
        }

        Product::query()
            ->where('id', $id)
            ->update([
                'category_id' => $categoryId
            ]);


        // 逻辑操作

        return $this->response()->success('分组修改成功')->refresh();
    }

    public function form()
    {
        // 获取外部传递参数
        $this->select('category_id')
            ->options(Category::selectOptions(null, '无分组'));

    }

    // 返回表单数据，如不需要可以删除此方法
    public function default()
    {
        // 获取外部传递参数
        $id = $this->payload['id'] ?? null;

        return [
            'category_id' => $this->payload['category_id'] ?? 0,
        ];
    }
}
