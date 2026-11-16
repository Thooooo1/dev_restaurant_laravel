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
        'image',
        'created_by',
        'updated_by',
        'new_p',
        'status',
    ];

    protected static ?bool $hasImageColumn = null;
    protected static ?bool $hasThumbnailColumn = null;

    protected function detectColumns(): void
    {
        if (static::$hasImageColumn === null) {
            try {
                static::$hasImageColumn = \Illuminate\Support\Facades\Schema::hasColumn('product', 'image');
                static::$hasThumbnailColumn = \Illuminate\Support\Facades\Schema::hasColumn('product', 'thumbnail');
            } catch (\Throwable $e) {
                static::$hasImageColumn = true;
                static::$hasThumbnailColumn = false;
            }
        }
    }

    protected static function booted(): void
    {
        static::creating(function ($product) {
            $product->detectColumns();
            if ($product->brand_id === null) {
                $product->brand_id = 1;
            }
            if ($product->new_p === null) {
                $product->new_p = 1;
            }
            if ($product->status === null) {
                $product->status = 1;
            }
            if ($product->created_by === null) {
                $product->created_by = 1;
            }
            if ($product->content === null) {
                $product->content = '';
            }
            if ($product->description === null) {
                $product->description = '';
            }
            if (empty($product->attributes['image']) && empty($product->attributes['thumbnail'])) {
                if (static::$hasImageColumn) {
                    $product->attributes['image'] = 'default.png';
                }
                if (static::$hasThumbnailColumn) {
                    $product->attributes['thumbnail'] = 'default.png';
                }
            }
        });
    }

    public function getImageAttribute()
    {
        return $this->attributes['image'] ?? $this->attributes['thumbnail'] ?? null;
    }

    public function getThumbnailAttribute()
    {
        return $this->attributes['image'] ?? $this->attributes['thumbnail'] ?? null;
    }

    public function setImageAttribute($value)
    {
        $this->detectColumns();
        if (static::$hasImageColumn) {
            $this->attributes['image'] = $value;
        }
        if (static::$hasThumbnailColumn) {
            $this->attributes['thumbnail'] = $value;
        }
        if (!static::$hasImageColumn && !static::$hasThumbnailColumn) {
            $this->attributes['image'] = $value;
        }
    }

    public function setThumbnailAttribute($value)
    {
        $this->detectColumns();
        if (static::$hasThumbnailColumn) {
            $this->attributes['thumbnail'] = $value;
        }
        if (static::$hasImageColumn) {
            $this->attributes['image'] = $value;
        }
        if (!static::$hasImageColumn && !static::$hasThumbnailColumn) {
            $this->attributes['thumbnail'] = $value;
        }
    }

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
