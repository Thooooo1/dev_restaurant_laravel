<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Product extends Model
{
    use SoftDeletes;
    protected $table = 'product';

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'content',
        'description',
        'price_buy',
        'price_sale',
        'qty',
        'thumbnail',
        'created_by',
        'updated_by',
        'new_p',
        'status',
    ];

    public function getPriceAttribute(): float
    {
        return (float) ($this->price_sale ?: $this->price_buy ?: 0);
    }
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

    /**
     * Đơn vị tính chuẩn F&B (Ly, Lon, Chai, Nồi, Đĩa, Phần, Gói)
     */
    public function getUnitAttribute(): string
    {
        $cat = mb_strtolower($this->category->name ?? '');
        $name = mb_strtolower($this->name ?? '');

        if (str_contains($cat, 'cafe') || str_contains($cat, 'sinh tố') || str_contains($cat, 'nước ép')) {
            return 'Ly';
        }
        if (str_contains($cat, 'bia') || str_contains($cat, 'nước ngọt')) {
            return (str_contains($name, 'chai') ? 'Chai' : 'Lon');
        }
        if (str_contains($cat, 'lẩu')) {
            return 'Nồi';
        }
        if (str_contains($cat, 'thuốc')) {
            return 'Gói';
        }
        if (str_contains($cat, 'rau') || str_contains($cat, 'xào') || str_contains($cat, 'chiên') || str_contains($cat, 'nướng')) {
            return 'Đĩa';
        }
        return 'Phần';
    }

    /**
     * Khu vực bếp chế biến in phiếu order (Quầy pha chế, Bếp nóng, Bếp nướng, Bếp lạnh / Khai vị)
     */
    public function getKitchenAreaAttribute(): string
    {
        $cat = mb_strtolower($this->category->name ?? '');

        if (str_contains($cat, 'cafe') || str_contains($cat, 'sinh tố') || str_contains($cat, 'nước ép') || str_contains($cat, 'nước ngọt') || str_contains($cat, 'bia') || str_contains($cat, 'thuốc')) {
            return 'Quầy pha chế';
        }
        if (str_contains($cat, 'nướng')) {
            return 'Bếp nướng';
        }
        if (str_contains($cat, 'lẩu') || str_contains($cat, 'xào') || str_contains($cat, 'chiên') || str_contains($cat, 'rau')) {
            return 'Bếp nóng';
        }
        return 'Bếp lạnh';
    }

}
