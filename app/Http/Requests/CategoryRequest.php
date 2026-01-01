<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
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

        if($this->isMethod('post')){
            return [
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255|unique:categories,slug',
                'description' => 'nullable|string',
                'parent_id' => 'nullable|exists:categories,id',
                'image'  => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
                'banner' => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
                'icon'   => 'nullable|image|mimes:png,svg,webp|max:1024',
                'is_featured' => 'boolean',
                // 'is_hot' => 'boolean',
                'status' => 'in:active,inactive,archived,deleted',

            ];
        }
        return [
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:categories,slug,',
            'description' => 'sometimes|string',
            'parent_id' => 'sometimes|exists:categories,id',
            'image'  => 'sometimes|image|mimes:png,jpg,jpeg,webp|max:2048',
            'banner' => 'sometimes|image|mimes:png,jpg,jpeg,webp|max:4096',
            'icon'   => 'sometimes|image|mimes:png,svg,webp|max:1024',
            'is_featured' => 'boolean',
                'status' => 'in:active,inactive,archived,deleted',
                
            // 'is_hot' => 'boolean',
            // 'is_active' => 'boolean',

        ];
    }
}
