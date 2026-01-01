<?php

namespace App\Http\Resources\Order;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,

            // الحالات
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'delivery_status' => $this->delivery_status,
            'payment_method' => $this->payment_method,

            // الأسعار
            'pricing' => [
                'sub_total' => $this->sub_total / 100,
                'tax_amount' => $this->tax_amount / 100,
                'total_amount' => $this->total_amount / 100,
            ],

            // العنوان
            'address' => $this->whenLoaded('address', function () {
                return [
                    'id' => $this->address->id,
                    'name' => $this->address->name,
                    'phone' => $this->address->phone,
                    'email' => $this->address->email,
                    'address' => $this->address->address,
                ];
            }),

            // العناصر
            'items' => OrderItemResource::collection($this->whenLoaded('items')),

            // عدد العناصر
            'items_count' => $this->when(
                $this->relationLoaded('items'),
                fn() => $this->items->sum('quantity')
            ),

            // معلومات إضافية
            'is_cancellable' => $this->isCancellable(),
            'is_paid' => $this->isPaid(),
            'is_delivered' => $this->isDelivered(),

            // الإلغاء
            'cancellation_reason' => $this->cancellation_reason,
            'cancelled_at' => $this->cancelled_at?->format('Y-m-d H:i:s'),

            // التواريخ
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
