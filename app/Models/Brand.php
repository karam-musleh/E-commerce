<?php

namespace App\Models;

// use App\Http\Traits\HasSlug;
// use App\HasSlug;
// use App\Http\Traits\HasSlug;
use App\Models\Product;
use App\Traits\HasSlug;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{

    use HasSlug;
    use HasStatus;
    //
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    protected $fillable = [
        'name',
        'slug',
        'status',
        'is_featured',
    ];

    // protected $attributes = [
    //     'status' => self::STATUS_ACTIVE,
    //     'is_featured' => false,
    // ];

    // Brand.php
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    // type can be 'brand_logo' or 'brand_banner'

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }
    public function logoImage()
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', 'brand_logo');
    }
    public function bannerImages()
    {
        return $this->morphMany(Image::class, 'imageable')->where('type', 'brand_banner');
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
        public function setStatus(string $status): bool
    {
        $allowed = [
            self::STATUS_ACTIVE,
            self::STATUS_INACTIVE,
        ];

        if (! in_array($status, $allowed)) {
            return false;
        }

        return $this->update(['status' => $status]);
    }
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
    public function scopeFeatured($query)
{
    return $query->where('is_featured', true);
}

}   
