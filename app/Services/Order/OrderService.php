<?php


// namespace App\Services\Order;

// use App\Models\Cart;
// use App\Models\Order;
// use App\Models\Address;
// use App\Models\Product;
// use App\Models\OrderItem;
// use Illuminate\Support\Str;
// use App\Models\OrderItemAttribute;
// use Illuminate\Support\Facades\DB;

// class OrderService
// {
//     /**
//      * إنشاء order من cart
//      *
//      * @param Cart $cart
//      * @param array $data يحتوي على address_id, payment_method
//      * @return Order
//      */
//     public function createOrderFromCart(array $data): Order
//     {
//         DB::beginTransaction();

//         try {
//             $cart = $this->getCart();

//             // ✅ 2. التحقق من السلة
//             $this->validateCart($cart);

//             // ✅ 3. جلب أو إنشاء العنوان
//             $address = $this->getOrCreateAddress($data);

//             // ✅ 4. حساب الأسعار
//             $pricing = $this->calculatePricing($cart, $data);

//             // ✅ 5. إنشاء الطلب
//             $order = $this->createOrder($address, $pricing, $data);

//             // ✅ 6. نسخ عناصر السلة للطلب
//             $this->copyCartItemsToOrder($cart, $order);

//             // ✅ 7. تحديث المخزون
//             $this->updateStock($order);

//             // ✅ 8. مسح أو تحديث السلة
//             $this->clearCart($cart);
//             DB::commit();

//             // ✅ 9. إرجاع الطلب مع العلاقات
//             return $order->fresh([
//                 'items.product',
//                 'items.attributes',
//                 'address'
//             ]);
//         } catch (\Exception $e) {
//             DB::rollBack();
//             throw $e; // يمكن التعامل مع الخطأ في Controller
//         }
//     }
//     protected function getCart(): Cart
//     {

//         $cart = auth('api')->user()->cart()
//             ->with([
//                 'items.product',
//                 'items.attributeValues.attribute'
//             ])
//             ->first();

//         if (!$cart || $cart->items->isEmpty()) {
//             abort(400, 'The cart is empty.');
//         }

//         return $cart;
//     }
//     protected function validateCart(Cart $cart): void
//     {
//         // تحقق أن السلة غير فارغة
//         if ($cart->items->isEmpty()) {
//             abort(400, 'The cart is empty.');
//         }

//         foreach ($cart->items as $item) {
//             $product = $item->product;

//             // تحقق من حالة المنتج
//             if ($product->status !== Product::STATUS_ACTIVE) {
//                 abort(400, "product {$product->name} is not available for sale.");
//             }

//             if ($product->total_quantity < $item->quantity) {
//                 abort(400, 'Insufficient stock for product: ' . $product->name);
//             }
//         }
//     }
//     protected function getOrCreateAddress(array $data): Address
//     {
//         // إذا في address_id موجود
//         if (isset($data['address_id'])) {
//             $address = Address::where('id', $data['address_id'])
//                 ->where('user_id', auth('api')->id())
//                 ->first();

//             if (!$address) {
//                 abort(404, 'Address not found');
//             }

//             return $address;
//         }

//         // إنشاء عنوان جديد
//         return Address::create([
//             'user_id' => auth('api')->id(),
//             'name' => $data['name'],
//             'phone' => $data['phone'],
//             'email' => $data['email'] ?? null,
//             'address' => $data['address'],
//         ]);
//     }

//     protected function calculatePricing(Cart $cart, array $data): array
//     {
//         // $subTotal = $cart->subtotal;
//         $subTotal = 0;
//         foreach ($cart->items as $item) {
//             $product = $item->product;
//             $unitPrice = intval($product->final_price * 100);
//             $additionalPrice = $item->additional_prices;
//             $totalPrice = ($unitPrice + $additionalPrice) * $item->quantity;
//             $subTotal += $totalPrice;
//         }

//         // الضريبة (مثلاً 16%)
//         $taxRate = 0.16;
//         $taxAmount = intval($subTotal * $taxRate);

//         // تكلفة الشحن (اختياري)
//         $shippingCost = 0; // يمكن تعديله حسب المنطقة

//         // المجموع النهائي
//         $totalAmount = $subTotal + $taxAmount + $shippingCost;

//         return [
//             'sub_total' => $subTotal,
//             'tax_amount' => $taxAmount,
//             'shipping_cost' => $shippingCost,
//             'total_amount' => $totalAmount,
//         ];
//     }
//     protected function createOrder(Address $address, array $pricing, array $data): Order
//     {
//         return Order::create([
//             'user_id' => auth('api')->id(),
//             'address_id' => $address->id,
//             // 'order_number' => (string) Str::ulid(), // ULID تلقائي
//             'sub_total' => $pricing['sub_total'],
//             'tax_amount' => $pricing['tax_amount'],
//             'total_amount' => $pricing['total_amount'],
//             'status' => Order::STATUS_PENDING,
//             'payment_method' => $data['payment_method'] ?? 'cash',
//             'payment_status' => Order::PAYMENT_UNPAID,
//             'delivery_status' => Order::DELIVERY_PENDING,
//         ]);
//     }
//     protected function copyCartItemsToOrder(Cart $cart, Order $order): void
//     {
//         foreach ($cart->items as $cartItem) {
//             $product = $cartItem->product;

//             // حساب السعر النهائي مع الـ attributes
//             $unitPrice = intval($product->final_price * 100);
//             $additionalPrice = $cartItem->attributeValues
//                 ->sum(fn($attr) => $attr->pivot->additional_price);
//             $totalPrice = ($unitPrice + $additionalPrice) * $cartItem->quantity;
//             $hasAttributes = $cartItem->attributeValues->isNotEmpty();

//             // إنشاء OrderItem
//             $orderItem = $order->items()->create([
//                 'product_id' => $product->id,
//                 'quantity' => $cartItem->quantity,
//                 'discount' => $product->discount ?? 0,
//                 'discount_type' => $product->discount_type ?? Product::DISCOUNT_TYPE_PERCENT,
//                 'unit_price' => $unitPrice,
//                 'total_price' => $totalPrice,
//             ]);

//             // نسخ الـ Attributes
//             if ($hasAttributes) {
//                 foreach ($cartItem->attributeValues as $attr) {
//                     $pivot = $product->attributeValues->where('id', $attr->id)->first();

//                     OrderItemAttribute::create([
//                         'order_item_id'        => $orderItem->id,
//                         'attribute_value_id'   => $attr->id,
//                         'attribute_name'       => $attr->attribute->name,
//                         'attribute_value'      => $attr->value,
//                         'additional_price'     => $pivot?->pivot?->additional_price ?? 0,
//                     ]);
//                 }
//             }
//         }
//     }
//     protected function updateStock(Order $order): void
//     {
//         foreach ($order->items as $orderItem) {
//             $product = $orderItem->product;

//             // تقليل الكمية المتوفرة
//             $product->decrement('total_quantity', $orderItem->quantity);

//             // تحديث كمية الـ attributes
//             foreach ($orderItem->itemAttributes ?? [] as $orderItemAttr) {
//                 DB::table('product_attribute_values')
//                     ->where('product_id', $product->id)
//                     ->where('attribute_value_id', $orderItemAttr->attribute_value_id)
//                     ->decrement('quantity', $orderItem->quantity);
//             }
//         }
//     }

//     protected function clearCart(Cart $cart): void
//     {
//         // خيار 1: مسح العناصر
//         $cart->items()->delete();

//         // خيار 2: تحديث الحالة
//         // $cart->update(['status' => Cart::STATUS_COMPLETED]);
//     }
//     public function cancelOrder(Order $order, string $reason = null): Order
//     {
//         DB::beginTransaction();
//         try {

//             // تحقق أن الطلب قابل للإلغاء
//             if (!$order->isCancellable()) {
//                 abort(400, 'This order cannot be cancelled.');
//             }

//             $order->load(['items.attributes']);

//             foreach ($order->items as $orderItem) {

//                 // إرجاع كمية المنتج
//                 DB::table('products')
//                     ->where('id', $orderItem->product_id)
//                     ->increment('total_quantity', $orderItem->quantity);

//                 // إرجاع كمية الخصائص
//                 foreach ($orderItem->attributes as $attr) {
//                     DB::table('product_attribute_values')
//                         ->where('product_id', $orderItem->product_id)
//                         ->where('attribute_value_id', $attr->attribute_value_id)
//                         ->increment('quantity', $orderItem->quantity);
//                 }
//             }

//             $order->update([
//                 'status' => Order::STATUS_CANCELLED,
//                 'cancellation_reason' => $reason,
//                 'cancelled_at' => now(),
//             ]);
//             DB::commit();

//             return $order->fresh();
//         } catch (\Exception $e) {
//             DB::rollBack();
//             throw $e;
//         }
//     }
//     public function updateOrderStatus(Order $order, string $status): Order
//     {
//         $validStatuses = [
//             Order::STATUS_PENDING,
//             Order::STATUS_CONFIRMED,
//             Order::STATUS_PROCESSING,
//             Order::STATUS_SHIPPED,
//             Order::STATUS_DELIVERED,
//             Order::STATUS_CANCELLED,
//         ];

//         if (!in_array($status, $validStatuses)) {
//             abort(400, 'Invalid order status');
//         }

//         $order->update(['status' => $status]);

//         return $order->fresh();
//     }
//     // updateDeliveryStatus
//     public function updateDeliveryStatus(Order $order, string $deliveryStatus): Order
//     {
//         $validStatuses = [
//             Order::DELIVERY_PENDING,
//             Order::DELIVERY_PROCESSING,
//             Order::DELIVERY_SHIPPED,
//             Order::DELIVERY_DELIVERED,
//         ];

//         if (!in_array($deliveryStatus, $validStatuses)) {
//             abort(400, 'Invalid delivery status');
//         }

//         $order->update(['delivery_status' => $deliveryStatus]);

//         return $order->fresh();
//     }
//     // updatePaymentStatus
//     public function updatePaymentStatus(Order $order, string $paymentStatus): Order
//     {
//         $validStatuses = [
//             Order::PAYMENT_PAID,
//             Order::PAYMENT_UNPAID,
//             Order::PAYMENT_FAILED,
//             Order::PAYMENT_REFUNDED,
//         ];

//         if (!in_array($paymentStatus, $validStatuses)) {
//             abort(400, 'Invalid payment status');
//         }

//         $order->update([
//             'payment_status' => $paymentStatus,
//             'paid_at' => $paymentStatus === Order::PAYMENT_PAID ? now() : null,
//         ]);

//         return $order->fresh();
//     }
// }


/**
 * حساب الـ totals للكارت
 *

 */
//     protected function calculateCartTotals(Cart $cart): array
//     {
//         $subTotal = 0;
//         $taxRate = 0.15; // 15% مثال ثابت

//         foreach ($cart->items as $item) {
//             $basePrice = $item->product->final_price;

//             $additional = $item->attributeValues->sum(fn($attr) => $attr->pivot->additional_price);

//             $itemTotal = ($basePrice + $additional) * $item->quantity;
//             $subTotal += $itemTotal;
//         }

//         $taxAmount = round($subTotal * $taxRate);
//         $totalAmount = $subTotal + $taxAmount;

//         return compact('subTotal', 'taxAmount', 'totalAmount');
//     }

//     /**
//      * أضف items و attributes لكل item إلى order
//      *
//      * @param Cart $cart
//      * @param Order $order
//      */
//     protected function addItemsToOrder(Cart $cart, Order $order): void
//     {
//         foreach ($cart->items as $item) {
//             $unitPrice = $item->product->final_price;
//             $additional = $item->attributeValues->sum(fn($attr) => $attr->pivot->additional_price);
//             $totalPrice = ($unitPrice + $additional) * $item->quantity;

//             $orderItem = OrderItem::create([
//                 'order_id'       => $order->id,
//                 'product_id'     => $item->product_id,
//                 'quantity'       => $item->quantity,
//                 'unit_price'     => $unitPrice,
//                 'total_price'    => $totalPrice,
//                 'discount'       => $item->product->discount,
//                 'discount_type'  => $item->product->discount_type,
//             ]);

//             // snapshot لكل attributes
//             $this->addAttributesToOrderItem($item, $orderItem);
//         }
//     }

//     /**
//      * أضف attributes لكل order item
//      *
//      * @param $cartItem
//      * @param OrderItem $orderItem
//      */
//     protected function addAttributesToOrderItem($cartItem, OrderItem $orderItem): void
//     {
//         foreach ($cartItem->attributeValues as $attr) {
//             $pivot = $cartItem->product->attributeValues->where('id', $attr->id)->first();

//             OrderItemAttribute::create([
//                 'order_item_id'        => $orderItem->id,
//                 'attribute_value_id'   => $attr->id,
//                 'attribute_name'       => $attr->attribute->name,
//                 'attribute_value'      => $attr->value,
//                 'additional_price'     => $pivot?->pivot?->additional_price ?? 0,
//             ]);
//         }
//     }
// }


namespace App\Services\Order;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Address;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\OrderItemAttribute;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * إنشاء order من cart
     */
    public function createOrderFromCart(array $data): Order
    {
        DB::beginTransaction();

        try {
            $cart = $this->getCart();
            // dd($cart);
            $this->validateCart($cart);

            $address = $this->getOrCreateAddress($data);

            $pricing = $this->calculatePricing($cart, $data);

            $order = $this->createOrder($address, $pricing, $data);

            $this->copyCartItemsToOrder($cart, $order);

            $this->updateStock($order);

            $this->clearCart($cart);

            DB::commit();

            // إرجاع الطلب مع كل العلاقات بشكل eager loaded
            return $order->fresh([
                'items.product',
                'items.itemAttributes',
                'address',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

  protected function getCart(): Cart
{

    $cart = auth('api')->user()->cart()
        ->with(['items.product', 'items.attributeValues.attribute'])
        ->first();

    if (!$cart) {
        abort(400, 'Cart not found for this user.');
    }

    // تحقق من وجود عناصر الكارت
    if ($cart->items->isEmpty()) {
        abort(400, 'The cart is empty.');
    }

    return $cart;
}


    protected function validateCart(Cart $cart): void
    {
        if ($cart->items->isEmpty()) {
            abort(400, 'The cart is empty.');
        }

        foreach ($cart->items as $item) {
            $product = $item->product;

            if ($product->status !== Product::STATUS_ACTIVE) {
                abort(400, "Product {$product->name} is not available for sale.");
            }

            if ($product->total_quantity < $item->quantity) {
                abort(400, "Insufficient stock for product: {$product->name}");
            }
        }
    }

    protected function getOrCreateAddress(array $data): Address
    {
        if (isset($data['address_id'])) {
            $address = Address::where('id', $data['address_id'])
                ->where('user_id', auth('api')->id())
                ->first();

            if (!$address) {
                abort(404, 'Address not found');
            }

            return $address;
        }

        return Address::create([
            'user_id' => auth('api')->id(),
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'] ?? null,
            'address' => $data['address'],
        ]);
    }


    protected function calculatePricing(Cart $cart, array $data): array
    {
        // ✅ 1. تحميل كل العلاقات مرة واحدة
        $cart->loadMissing([
            'items.product:id,main_price,discount,discount_type',
            'items.attributeValues:id'
        ]);

        // ✅ 2. حساب المجموع الفرعي
        $subTotal = $cart->items->sum(function ($item) {
            // السعر الأساسي (محوّل لـ cents)
            $unitPrice = intval($item->product->final_price * 100);

            // السعر الإضافي (مع التحقق من الوجود)
            $additionalPrice = 0;
            if ($item->relationLoaded('attributeValues') && $item->attributeValues) {
                $additionalPrice = $item->attributeValues->sum(
                    fn($attr) => $attr->pivot->additional_price ?? 0
                );
            }

            // السعر الإجمالي للـ item
            return ($unitPrice + $additionalPrice) * $item->quantity;
        });

        // ✅ 3. حساب الضريبة والشحن
        // 0.16 يمثل 16% كمثال ثابت
        $taxRate = 0.10;
        $taxAmount = intval($subTotal * $taxRate);
        $shippingCost = 0;
        $totalAmount = $subTotal + $taxAmount + $shippingCost;

        return [
            'sub_total' => $subTotal,
            'tax_amount' => $taxAmount,
            'shipping_cost' => $shippingCost,
            'total_amount' => $totalAmount,
        ];
    }
    protected function createOrder(Address $address, array $pricing, array $data): Order
    {
        return Order::create([
            'user_id' => auth('api')->id(),
            'address_id' => $address->id,
            'sub_total' => $pricing['sub_total'],
            'tax_amount' => $pricing['tax_amount'],
            'total_amount' => $pricing['total_amount'],
            'status' => Order::STATUS_PENDING,
            'payment_method' => $data['payment_method'] ?? 'cash',
            'payment_status' => Order::PAYMENT_UNPAID,
            'delivery_status' => Order::DELIVERY_PENDING,
        ]);
    }

    protected function copyCartItemsToOrder(Cart $cart, Order $order): void
    {
        foreach ($cart->items as $cartItem) {
            $product = $cartItem->product;

            $unitPrice = $product->final_price * 100 ;
            $additionalPrice = $cartItem->attributeValues->sum(fn($attr) => $attr->pivot->additional_price);
            $totalPrice = ($unitPrice + $additionalPrice) * $cartItem->quantity;

            $orderItem = $order->items()->create([
                'product_id' => $product->id,
                'quantity' => $cartItem->quantity,
                'discount' => $product->discount ?? 0,
                'discount_type' => $product->discount_type ?? Product::DISCOUNT_TYPE_PERCENT,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ]);

            foreach ($cartItem->attributeValues as $attr) {
                $pivot = $product->attributeValues->where('id', $attr->id)->first();

                OrderItemAttribute::create([
                    'order_item_id' => $orderItem->id,
                    'attribute_value_id' => $attr->id,
                    'attribute_name' => $attr->attribute->name,
                    'attribute_value' => $attr->value,
                    'additional_price' => $pivot?->pivot?->additional_price ?? 0,
                ]);
            }
        }
    }
//     protected function copyCartItemsToOrder(Cart $cart, Order $order): void
// {
//     foreach ($cart->items as $cartItem) {
//         $product = $cartItem->product;

//         // السعر الأساسي
//         $unitPrice = intval($product->final_price * 100);
//         $additionalPrice = $cartItem->attributeValues->sum(fn($attr) => $attr->pivot->additional_price);
//         $finalUnitPrice = $unitPrice + $additionalPrice;

//         // ✅ حساب الخصم
//         $discount = $product->discount ?? 0;
//         $discountType = $product->discount_type ?? Product::DISCOUNT_TYPE_PERCENT;

//         $priceAfterDiscount = $finalUnitPrice;
//         if ($discount > 0) {
//             if ($discountType === 'percent') {
//                 $priceAfterDiscount = $finalUnitPrice * (1 - $discount / 100);
//             } else { // fixed
//                 $priceAfterDiscount = $finalUnitPrice - ($discount * 100);
//             }
//         }

//         // ✅ السعر الإجمالي بعد الخصم
//         $totalPrice = intval($priceAfterDiscount * $cartItem->quantity);

//         $orderItem = $order->items()->create([
//             'product_id' => $product->id,
//             'quantity' => $cartItem->quantity,
//             'discount' => $discount,
//             'discount_type' => $discountType,
//             'unit_price' => $finalUnitPrice, // قبل الخصم
//             'total_price' => $totalPrice, // ✅ بعد الخصم
//         ]);

//         // الـ attributes زي ما هي
//         foreach ($cartItem->attributeValues as $attr) {
//             $pivot = $product->attributeValues->where('id', $attr->id)->first();

//             OrderItemAttribute::create([
//                 'order_item_id' => $orderItem->id,
//                 'attribute_value_id' => $attr->id,
//                 'attribute_name' => $attr->attribute->name,
//                 'attribute_value' => $attr->value,
//                 'additional_price' => $pivot?->pivot?->additional_price ?? 0,
//             ]);
//         }
//     }
// }

    protected function updateStock(Order $order): void
    {
        foreach ($order->items as $orderItem) {
            $product = $orderItem->product;

            $product->decrement('total_quantity', $orderItem->quantity);

            $attributeIds = $orderItem->itemAttributes->pluck('attribute_value_id')->toArray();

            if (!empty($attributeIds)) {
                DB::table('product_attribute_values')
                    ->where('product_id', $product->id)
                    ->whereIn('attribute_value_id', $attributeIds)
                    ->decrement('quantity', $orderItem->quantity);
            }
        }
    }

    protected function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    public function cancelOrder(Order $order, string $reason = null): Order
    {
        DB::beginTransaction();
        try {
            if (!$order->isCancellable()) {
                abort(400, 'This order cannot be cancelled.');
            }

            $order->load(['items.itemAttributes']);

            foreach ($order->items as $orderItem) {
                DB::table('products')
                    ->where('id', $orderItem->product_id)
                    ->increment('total_quantity', $orderItem->quantity);

                $attributeIds = $orderItem->itemAttributes->pluck('attribute_value_id')->toArray();

                if (!empty($attributeIds)) {
                    DB::table('product_attribute_values')
                        ->where('product_id', $orderItem->product_id)
                        ->whereIn('attribute_value_id', $attributeIds)
                        ->increment('quantity', $orderItem->quantity);
                }
            }

            $order->update([
                'status' => Order::STATUS_CANCELLED,
                'cancellation_reason' => $reason,
                'cancelled_at' => now(),
            ]);

            DB::commit();

            return $order->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateOrderStatus(Order $order, string $status): Order
    {
        $validStatuses = [
            Order::STATUS_PENDING,
            Order::STATUS_CONFIRMED,
            Order::STATUS_PROCESSING,
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_CANCELLED,
        ];

        if (!in_array($status, $validStatuses)) {
            abort(400, 'Invalid order status');
        }

        $order->update(['status' => $status]);

        return $order->fresh();
    }

    public function updateDeliveryStatus(Order $order, string $deliveryStatus): Order
    {
        $validStatuses = [
            Order::DELIVERY_PENDING,
            Order::DELIVERY_PROCESSING,
            Order::DELIVERY_SHIPPED,
            Order::DELIVERY_DELIVERED,
        ];

        if (!in_array($deliveryStatus, $validStatuses)) {
            abort(400, 'Invalid delivery status');
        }

        $order->update(['delivery_status' => $deliveryStatus]);

        return $order->fresh();
    }

    public function updatePaymentStatus(Order $order, string $paymentStatus): Order
    {
        $validStatuses = [
            Order::PAYMENT_PAID,
            Order::PAYMENT_UNPAID,
            Order::PAYMENT_FAILED,
            Order::PAYMENT_REFUNDED,
        ];

        if (!in_array($paymentStatus, $validStatuses)) {
            abort(400, 'Invalid payment status');
        }

        $order->update([
            'payment_status' => $paymentStatus,
            'paid_at' => $paymentStatus === Order::PAYMENT_PAID ? now() : null,
        ]);

        return $order->fresh();
    }
}
