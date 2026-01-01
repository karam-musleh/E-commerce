<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UpdateCartService
{
    public function updateQuantity(array $data)
    {
        DB::beginTransaction();

        try {
            $product = Product::where('id', $data['product_id'])
                ->where('status', 'active')
                ->firstOrFail();

            $productAttributeValues = DB::table('product_attribute_values')
                ->where('product_id', $product->id)
                ->whereIn('attribute_value_id', $data['attribute_value_ids'])
                ->get();

            if ($productAttributeValues->count() !== count($data['attribute_value_ids'])) {
                abort(400, 'Invalid attributes selected');
            }

            $maxAvailable = $productAttributeValues->min('quantity');

            if ($data['quantity'] > $maxAvailable) {
                abort(400, 'Quantity exceeds available stock');
            }

            foreach ($productAttributeValues as $attr) {
                if ($data['quantity'] < $attr->min_qty) {
                    abort(400, 'Minimum quantity not met');
                }
            }

            $cart = Cart::where('user_id', auth('api')->id())
                ->where('status', 'active')
                ->firstOrFail();

            $incomingAttrIds = collect($data['attribute_value_ids'])
                ->sort()
                ->values()
                ->toArray();

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

            if (! $matchedItem) {
                abort(404, 'Cart item not found');
            }

            if ($data['quantity'] == 0) {
                $matchedItem->delete();
            } else {
                $matchedItem->update([
                    'quantity' => $data['quantity'],
                ]);
            }

            DB::commit();

            return $cart->load([
                'items.product',
                'items.attributeValues'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            abort(500, $e->getMessage());
        }
    }
}
