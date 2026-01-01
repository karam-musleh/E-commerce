<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DailyDeal extends Model
{

    //
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_EXPIRED = 'expired';
    const DISCOUNT_TYPE_PERCENT = 'percent';
    const DISCOUNT_TYPE_FIXED = 'fixed';

    protected $fillable = [
        'product_id',
        'discount',
        'discount_type',
        'starts_at',
        'ends_at',
        'status',
    ];
    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }


    public function scopeActive($query)
    {
        return $query->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now())
            ->where('status', self::STATUS_ACTIVE);
    }
    public function scopeUpcoming($query)
    {
        return $query->where('starts_at', '>', now());
    }
    public function scopeExpired($query)
    {
        return $query->where('ends_at', '<', now());
    }
    // public function isActive()
    // {
    //     return $this->starts_at <= now() &&
    //         $this->ends_at >= now() &&
    //         $this->status === self::STATUS_ACTIVE;
    // }
    protected function isActive(): Attribute
    {
        return Attribute::make(
            get: fn() =>
            $this->starts_at <= now() &&
                $this->ends_at >= now() &&
                $this->status === self::STATUS_ACTIVE
        );
    }
    protected function isExpired(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->ends_at < now()
        );
    }


    protected function state(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->ends_at < now()) {
                    return self::STATUS_EXPIRED;
                }

                return $this->status;
            }
        );
    }

    //

    // protected function finalPrice(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn() =>
    //         $this->product ? $this->calculateFinalPrice() : null
    //     );
    // }

    // private function calculateFinalPrice(): float
    // {
    //     $price = $this->product->main_price;

    //     if ($this->discount) {
    //         if ($this->discount_type === self::DISCOUNT_TYPE_PERCENT) {
    //             $price -= $price * ($this->discount / 100);
    //         } elseif ($this->discount_type === self::DISCOUNT_TYPE_FIXED) {
    //             $price -= $this->discount;
    //         }
    //     }

    //     return max($price, 0);
    // }
    protected function finalPrice(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->product) {
                    return null;
                }

                $price = $this->product ? $this->product->final_price : 0;

                if ($this->discount) {
                    if ($this->discount_type === self::DISCOUNT_TYPE_PERCENT) {
                        $price -= $price * ($this->discount / 100);
                    } elseif ($this->discount_type === self::DISCOUNT_TYPE_FIXED) {
                        $price -= $this->discount;
                    }
                }

                return max($price, 0);
            }
        );
    }
}
