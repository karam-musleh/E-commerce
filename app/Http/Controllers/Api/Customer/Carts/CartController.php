<?php

namespace App\Http\Controllers\Api\Customer\Carts;

use App\Models\Cart;
use App\Services\Cart\AddCartService;
use App\Traits\ApiResponserTrait;
use App\Http\Requests\CartRequest;
use App\Services\Cart\RemoveCartService;
use App\Services\Cart\UpdateCartService;
use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;

class CartController extends Controller
{
    use ApiResponserTrait;
    protected AddCartService $addCartService;
    protected UpdateCartService $updateCartService;
    protected RemoveCartService $removeCartService;


    public function __construct(
        AddCartService $addCartService,
        UpdateCartService $updateCartService,
        RemoveCartService $removeCartService
    ) {
        $this->addCartService = $addCartService;
        $this->updateCartService = $updateCartService;
        $this->removeCartService = $removeCartService;
    }


    public function add(CartRequest $request)
    {
        $data = $request->validated();
        if(!isset($data['attribute_value_ids'])){
            $data['attribute_value_ids'] = [];
        }
        sort($data['attribute_value_ids']);

        $cart = $this->addCartService->addToCart($data);

        // dd($cart);
        return $this->successResponse(
            new CartResource($cart),
            'Item added to cart successfully',

        );
    }
    // show cart

    /**
     * 🛒 Show current cart (User / Guest)
     */
    public function show()
    {
        // ✅ User logged in


        $cart = Cart::with([
            'items.product.attributeValues.attribute',
            'items.attributeValues'
        ])
            ->where('user_id', auth('api')->id())
            ->where('status', 'active')
            ->first();

        if (! $cart || $cart->items->isEmpty()) {
            return $this->successResponse([
                [],
                'Cart is empty'
            ]);
        }
        return $this->successResponse(
            new CartResource($cart),
            'Cart retrieved successfully',
        );
    }
    public function update(CartRequest $request)
    {
        $data = $request->validated();
        sort($data['attribute_value_ids']);
        $cart = $this->updateCartService->updateQuantity($data);
        return $this->successResponse(
            new CartResource($cart),
            'Cart item quantity updated successfully',
        );
    }
    public function remove(CartRequest $request)
    {
        $data = $request->validated();
        if(isset($data['attribute_value_ids'])){
            sort($data['attribute_value_ids']);
        }
        $cart = $this->removeCartService->removeItem($data);
        return $this->successResponse(
            new CartResource($cart),
            'Cart item removed successfully',
        );
    }
}
