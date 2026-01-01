<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DailyProductRequest extends FormRequest
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

// product_id
// discount
// discount_type
// starts_at
// ends_at
// status
if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {

            return [
                'product_id' => 'sometimes|exists:products,id',
                'discount' => 'nullable|numeric|min:0',
                'discount_type' => 'nullable|in:percent,fixed',
                'starts_at' => 'sometimes|date',
                'ends_at' => 'sometimes|date|after:starts_at',
                'status' => 'nullable',
            ];
        }

        return [
            'product_id' => 'required|exists:products,id',
            'discount' => 'nullable|numeric|min:0',
            'discount_type' => 'nullable|in:percent,fixed',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'status' => 'nullable',
                    // dd($this->all())

        ];
    }
}
