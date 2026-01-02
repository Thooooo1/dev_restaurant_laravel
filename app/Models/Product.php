<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Product extends Model
{
    use SoftDeletes;
    protected $table ='product';
    public function category():BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    public function brand():BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function reviewCount()
    {
        return $this->reviews()->count();
    }
    public function favoritedBy()
    {
        return $this->hasMany(Favorite::class);
    }
    public function add(Product $product)
    {
        auth()->user()->favorites()->attach($product->id);
        return back();
    }

    public function remove(Product $product)
    {
        auth()->user()->favorites()->detach($product->id);
        return back();
    }

}
