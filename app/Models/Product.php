<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Product extends Model
{
    use HasSlug;

    /*------------------------------------
    | Constants
    ------------------------------------*/
    const DISCOUNT_TYPE_PERCENT = 'percent';
    const DISCOUNT_TYPE_FIXED   = 'fixed';

    const STATUS_ACTIVE   = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ARCHIVED = 'archived';

    /*------------------------------------
    | Fillable
    ------------------------------------*/
    protected $fillable = [
        'name',
        'slug',
        'description',
        'category_id',
        'brand_id',
        'main_price',
        'discount',
        'discount_type',
        'total_quantity',
        'status',
        'min_qty',
        'is_featured',
        'unit',
        'weight',
    ];
    protected $attributes = [
        'status' => 'active',
        'discount_type' => 'percent',
    ];
    protected $with = ['attributeValues.attribute'];



    /*------------------------------------
    | Relationships
    ------------------------------------*/
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public function dailyDeal()
    {
        return $this->hasMany(DailyDeal::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_attribute_values')
            ->withPivot([
                'additional_price',
                'quantity',
                'min_qty',
            ])
            ->withTimestamps();
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }


    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function mainImage()
    {
        return $this->morphOne(Image::class, 'imageable')->where('type', 'main');
    }

    public function galleryImages()
    {
        return $this->morphMany(Image::class, 'imageable')->where('type', 'gallery');
    }

    // scope Active
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
    /*------------------------------------
    | Accessors (new style)
    ------------------------------------*/

    protected function mainPrice(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value / 100,
            set: fn($value) => intval($value * 100)
        );
    }


    protected function finalPrice(): Attribute
    {
        return Attribute::make(
            get: function () {

                $price = $this->main_price;

                // Apply discount
                if ($this->discount) {
                    if ($this->discount_type === self::DISCOUNT_TYPE_PERCENT) {
                        $price -= $price * ($this->discount / 100);
                    } elseif ($this->discount_type === self::DISCOUNT_TYPE_FIXED) {
                        $price -= $this->discount;
                    }
                }

                if ($price < 0) {
                    $price = 0;
                }

                return $price;
            }
        );
    }

    /** Get additional price from pivot (converted) */
    protected function additionalPrice(): Attribute
    {
        return Attribute::make(
            get: fn() =>
            $this->pivot ? $this->pivot->additional_price / 100 : null
        );
    }



    protected function mainImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() =>
            $this->mainImage
                ? Storage::disk('custom')->url($this->mainImage->path)
                : null
        );
    }

    protected function galleryImagesUrls(): Attribute
    {
        return Attribute::make(
            get: fn() =>
            $this->galleryImages->map(
                fn($img) => Storage::disk('custom')->url($img->path)
            )
        );
    }

    protected function imagesUrls(): Attribute
    {
        return Attribute::make(
            get: fn() =>
            $this->images->map(
                fn($img) => Storage::disk('custom')->url($img->path)
            )
        );
    }
    // scope approved reviews
    public function approvedReviews()
    {
        return $this->reviews()->where('status', Review::STATUS_APPROVED);
    }
    // Average rating from approved reviews
    protected function averageRating(): Attribute
    {
        return Attribute::make(
            get: fn() => round(
                $this->approvedReviews()->avg('rating') ?? 0,
                1
            )
        );
    }

    protected function reviewsCount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->approvedReviews()->count()
        );
    }



    public function getRouteKeyName()
    {
        return 'slug';
    }
}
