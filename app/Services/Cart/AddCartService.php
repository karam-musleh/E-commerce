<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class AddCartService
{
    public function addToCart(array $data)
    {
        try {
            DB::beginTransaction();

            $product = Product::where('id', $data['product_id'])
                ->where('status', 'active')
                ->firstOrFail();

            // 2️⃣ تحقق من attributes لو موجودة
            $incomingAttrIds = collect($data['attribute_value_ids'] ?? [])
                ->sort()
                ->values()
                ->toArray();

            $productAttributeValues = [];
            if (!empty($incomingAttrIds)) {
                $productAttributeValues = DB::table('product_attribute_values')
                    ->where('product_id', $product->id)
                    ->whereIn('attribute_value_id', $incomingAttrIds)
                    ->get();

                if ($productAttributeValues->count() !== count($incomingAttrIds)) {
                    abort(400, 'Invalid attributes selected');
                }

                // 3️⃣ تحقق من الكمية حسب أقل مخزون لكل attribute
                $maxAvailable = $productAttributeValues->min('quantity');

                if ($data['quantity'] > $maxAvailable) {
                    abort(400, 'Quantity exceeds available stock');
                }

                foreach ($productAttributeValues as $attr) {
                    if ($data['quantity'] < $attr->min_qty) {
                        abort(400, 'Minimum quantity not met');
                    }
                }
            }

            // 4️⃣ إضافة للـ cart أو زيادة الكمية
            $cart = $this->addToDatabaseCart($data, $incomingAttrIds);

            DB::commit();
            return $cart;
        } catch (\Exception $e) {
            DB::rollBack();
            abort(500, 'Failed to add to cart: ' . $e->getMessage());
        }
    }

    protected function addToDatabaseCart(array $data, array $incomingAttrIds)
    {
        $cart = Cart::firstOrCreate([
            'user_id' => auth('api')->id(),
            'status' => 'active'
        ]);

        // جلب كل items لنفس المنتج
        $items = $cart->items()
            ->where('product_id', $data['product_id'])
            ->with('attributeValues')
            ->get();

        $matchedItem = null;

        foreach ($items as $item) {
            $existingAttrIds = $item->attributeValues
                ->pluck('id')
                ->sort()
                ->values()
                ->toArray();

            if ($existingAttrIds === $incomingAttrIds) {
                $matchedItem = $item;
                break;
            }
        }

        // ✅ نفس المنتج + نفس attributes → زيادة الكمية
        if ($matchedItem) {
            $matchedItem->increment('quantity', $data['quantity']);
        }
        // 🆕 item جديد
        else {
            $matchedItem = $cart->items()->create([
                'product_id' => $data['product_id'],
                'quantity'   => $data['quantity'],
            ]);

            if (!empty($incomingAttrIds)) {
                $matchedItem->attributeValues()->sync($incomingAttrIds);
            }
        }

        return $cart->load([
            'items.product.attributeValues.attribute',
            'items.attributeValues'
        ]);
    }
}
