<?php

namespace App\Services\Cart;

use App\Models\Cart;

class RemoveCartService
{
    public function removeItem(array $data)
    {
        $cart = Cart::where('user_id', auth('api')->id())
            ->where('status', 'active')
            ->firstOrFail();

        // ترتيب attributes القادمة
        $incomingAttrIds = collect($data['attribute_value_ids'] ?? [])
            ->sort()
            ->values()
            ->toArray();

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

        if (! $matchedItem) {
            abort(404, 'Cart item not found');
        }

        // حذف item + pivot (cascade)
        $matchedItem->delete();

        return $cart->load([
            'items.product',
            'items.attributeValues'
        ]);
    }
}
