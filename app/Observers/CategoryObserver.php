<?php

namespace App\Observers;

use App\Models\Category;
use App\Models\Product;

class CategoryObserver
{
    public function saved()
    {
        Category::cacheKeyword();
    }

    public function deleted(Category $category)
    {
        $id = $category->id;
        Product::query()
            ->where('category_id', $id)
            ->update([
                'category_id' => 0
            ]);
    }
}
