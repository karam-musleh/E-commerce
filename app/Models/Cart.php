<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }
    protected function subtotal(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->items->sum('total_price')
        );
    }

    protected function itemsCount(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->items->sum('quantity')
        );
    }
    protected function totalSavings(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->items->sum('savings')  // ✅ بسيطة!
        );
    }
    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    public function clear()
    {
        $this->items()->delete();
    }


    // scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
