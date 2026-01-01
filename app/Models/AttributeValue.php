<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasSlug;
    //
    protected $fillable = [
        'attribute_id',
        'value',
        'slug',
    ];
        protected $slugFrom = 'value';

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attribute_values')
            ->withPivot('additional_price')
            ->withTimestamps();
    }
    public function cartItems()
    {
        return $this->belongsToMany(CartItem::class, 'cart_item_attribute_values')
            ->withTimestamps();
    }
}
