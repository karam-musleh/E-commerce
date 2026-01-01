<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemAttribute extends Model
{
    //

    protected $fillable = [
        'order_item_id',
        'attribute_value_id',
        'attribute_name',
        'attribute_value',
        'additional_price',
    ];
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
    

}
