<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    //
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'discount',
        'discount_type',
    ];
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
     public function itemAttributes()
    {
        return $this->hasMany(OrderItemAttribute::class);
    }
    public function calculateTotalPrice(): int
    {
        $basePrice = $this->unit_price;
        $additional = $this->itemAttributes->sum('additional_price');
        return ($basePrice + $additional) * $this->quantity;
    }

    // Snapshot للـ attributes عند إنشاء الـ order item
    public function snapshotAttributes(array $attributes): void
    {
        foreach ($attributes as $attr) {
            $this->itemAttributes()->create([
                'attribute_value_id' => $attr['id'] ?? null,
                'attribute_name' => $attr['name'],
                'attribute_value' => $attr['value'],
                'additional_price' => $attr['additional_price'] ?? 0,
            ]);
        }
    }
    // داخل OrderItem.php
public function getAttributesAttribute()
{
    return $this->itemAttributes;
}


    // Renamed relation to avoid collision with Eloquent's $attributes property

}
