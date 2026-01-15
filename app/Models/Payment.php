<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    //
    const STATUS_PENDING    = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PAID       = 'paid';
    const STATUS_FAILED     = 'failed';
    const STATUS_CANCELLED  = 'cancelled';
    const STATUS_REFUNDED   = 'refunded';


    protected $fillable = [
        'order_id',
        'user_id',
        'payment_reference',
        'amount',
        'currency',
        'payment_method',
        'payment_gateway',
        'status',
        'transaction_id',
        'gateway_reference',
        'paid_at',
        'failed_at',
        'gateway_response',
        'failure_reason',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    protected static function booted()
    {
        static::creating(function ($payment) {
            $payment->payment_reference = (string) Str::ulid();
        });
    }
}
