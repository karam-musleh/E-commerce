<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItemAttributeValue extends Model
{
    //
    protected $fillable = [
        'cart_item_id',
        'attribute_value_id',
        'additional_price',
    ];
    public function cartItem()
    {
        return $this->belongsTo(CartItem::class);
    }
    public function attributeValue()
    {
        return $this->belongsTo(AttributeValue::class);
    }
}
