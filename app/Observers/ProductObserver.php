<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductLog;
use Carbon\Carbon;
use Illuminate\Support\Arr;

class ProductObserver
{
    public function saved(Product $product)
    {
        $dirtys = $product->getDirty();
        $origin = $product->getOriginal();

        if ($dirtys && !data_get($dirtys, 'id')) {

            $fields = Arr::only($dirtys, array_keys(Product::LOG_FIELDS));
            $date = Carbon::now()->toDateTimeString();
            foreach ($fields as $key => $dirty) {
                $originValue = data_get($origin, $key);
                ProductLog::create([
                    "product_id" => $product->id,
                    "date" => $date,
                    "field" => $key,
                    "action" => "从 {$originValue} 变更为 {$dirty}",
                ]);
            }
        }

    }
}
