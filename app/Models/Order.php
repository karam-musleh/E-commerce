<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Order extends Model
{
    // use HasUlids;

    const STATUS_PENDING = 'pending';           // في الانتظار
    const STATUS_CONFIRMED = 'confirmed';       // تم التأكيد
    const STATUS_PROCESSING = 'processing';     // قيد المعالجة
    const STATUS_SHIPPED = 'shipped';          // تم الشحن
    const STATUS_DELIVERED = 'delivered';      // تم التسليم
    const STATUS_CANCELLED = 'cancelled';      // ملغي

    const PAYMENT_UNPAID = 'unpaid';           // غير مدفوع
    const PAYMENT_PAID = 'paid';               // مدفوع
    const PAYMENT_FAILED = 'failed';           // فشل الدفع
    const PAYMENT_REFUNDED = 'refunded';       // تم الاسترجاع

    const DELIVERY_PENDING = 'pending';        // في الانتظار
    const DELIVERY_PROCESSING = 'processing';  // قيد المعالجة
    const DELIVERY_SHIPPED = 'shipped';        // تم الشحن
    const DELIVERY_DELIVERED = 'delivered';    // تم التسليم





    protected $fillable = [
        'user_id',
        'address_id',
        'order_number',
        'sub_total',
        'tax_amount',
        'total_amount',
        'status',
        'cancellation_reason',
        'payment_method',
        'delivery_status',
        'payment_status',
    ];
    protected $casts = [
        'sub_total' => 'integer',
        'tax_amount' => 'integer',
        'total_amount' => 'integer',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];
    protected static function boot()
    {
        parent::boot();

        // ✅ توليد order_number تلقائياً عند الإنشاء
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = (string) Str::ulid();
            }
        });
    }
    // public $incrementing = true;
    // protected $keyType = 'int';

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function successfulPayment()
    {
        return $this->hasOne(Payment::class)
            ->where('status', 'paid')
            ->latestOfMany();
    }

    /**
     * تحقق إذا الطلب مدفوع
     */
    public function isPaid(): bool
    {
        return $this->payment_status === self::PAYMENT_PAID;
    }

    /**
     * تحقق إذا الطلب تم توصيله
     */
    public function isDelivered(): bool
    {
        return $this->delivery_status === self::DELIVERY_DELIVERED;
    }

    /*------------------------------------
    | Scopes
    ------------------------------------*/

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', self::PAYMENT_PAID);
    }

    /*------------------------------------
    | Route Key
    ------------------------------------*/

    /**
     * استخدام order_number في الـ URLs
     */
    public function getRouteKeyName()
    {
        return 'order_number';
    }
}
