<?php

namespace App\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;
    use ModelTree;
    use HasDateTimeFormatter;

    const CACHE_KEY = '__category_id_keyword_cache_key__';

    public $timestamps = false;
    protected $fillable = [
        'parent_id',
        'order',
        'title',
        'keyword',
    ];


    public function subcategories()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('subcategories');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function getAllChildren(): \Illuminate\Support\Collection
    {
        $sections = collect([$this]);

        foreach ($this->children as $section) {
//            $sections->push($section);
            $sections = $sections->merge($section->getAllChildren());
        }


        return $sections;
    }

    public static function allChildrenOfId($id)
    {
        $children = Category::query()
            ->where('id', $id)
            ->first()
            ->getAllChildren();

        return $children;
    }
    // [ 0 ,1 ,2 ,3 ,4 ,5, 6 ]
    // parent_id != [ 0 ,1 ,2 ,3 ,4 ,5 ,6 ]
    //
    // [3 ,4 ,5 ,6]

    public static function getKeywords()
    {
        $value = Cache::get(self::CACHE_KEY);
        if (!$value) {
            return static::cacheKeyword();
        }

        return collect(json_decode($value, true));
    }

    public static function validateKeyword($text)
    {
        $keyword = static::getKeywords();
        $item = $keyword->first(function ($item) use ($text) {
            return preg_match("/({$item})/", $text);
        });

        return $item ? $item['id'] : 0;
    }

    public static function cacheKeyword()
    {
        $notChildren = Category::query()
            ->select(['parent_id', 'id', 'keyword'])
            ->where('keyword', '<>', '')
            ->has('children', '=', 0)
            ->get();

        $data = $notChildren->map(function ($val) {
            $val['keyword'] = str_replace(',', '|', $val);
            return $val;
        });

        Cache::put(self::CACHE_KEY, $data->toJson());

        return $data;
    }


}
