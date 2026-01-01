<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    //
    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'discount_type',
    ];
    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'integer',
        'discount' => 'integer',
    ];
    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(
            AttributeValue::class,
            'cart_item_attribute_values',
            'cart_item_id',
            'attribute_value_id'
        );
    }


    public function getFinalPriceAttribute()
    {
        $basePrice = $this->product->final_price;

        $additional = $this->attributeValues
            ->sum(fn($val) => $val->pivot?->additional_price ?? $val->additional_price);

        return ($basePrice + $additional) * $this->quantity;
    }
}
