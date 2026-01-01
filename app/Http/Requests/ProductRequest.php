<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {

            return [
                'name' => 'sometimes|string|max:255|unique:products,name,' . $this->route('product'),
                'slug' => 'nullable|string|max:255|unique:products,slug',
                'description' => 'nullable|string',
                'category_id' => 'sometimes|exists:categories,id',
                'brand_id' => 'nullable|exists:brands,id',
                'main_price' => 'sometimes|numeric|min:0',
                'discount' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:percent,fixed',
                'total_quantity' => 'nullable|integer|min:0',
                'status' => 'nullable',
                'min_qty' => 'nullable|integer|min:1',
                'is_featured' => 'nullable|boolean',
                'unit' => 'nullable|string|max:50',
                'weight' => 'nullable|numeric|min:0',
                'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'attribute_values' => 'nullable|array',
                'attribute_values.*.id' => 'sometimes|exists:attribute_values,id',
                'attribute_values.*.additional_price' => 'nullable|numeric|min:0',
                'attribute_values.*.quantity' => 'sometimes|integer|min:0',
                'attribute_values.*.min_qty' => 'nullable|integer|min:1',

            ];
        }

        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'main_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percent,fixed',
            'total_quantity' => 'nullable|integer|min:0',
            'status' => 'nullable',
            'min_qty' => 'nullable|integer|min:1',
            'is_featured' => 'nullable|boolean',
            'unit' => 'nullable|string|max:50',
            'weight' => 'nullable|numeric|min:0',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'attribute_values' => 'nullable|array',
            'attribute_values.*.id' => 'sometimes|exists:attribute_values,id',
            'attribute_values.*.additional_price' => 'nullable|numeric|min:0',
            'attribute_values.*.quantity' => 'required|integer|min:0',
            'attribute_values.*.min_qty' => 'nullable|integer|min:1',

        ];
    }
}
