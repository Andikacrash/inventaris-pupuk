<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
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
        return [
            'product_id' => 'required|exists:products,id',
            'adjustment_type' => 'required|in:increase,decrease,set',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'product_id.required' => 'Product ID harus diisi',
            'product_id.exists' => 'Product tidak ditemukan',
            'adjustment_type.required' => 'Tipe adjustment harus dipilih',
            'adjustment_type.in' => 'Tipe adjustment harus increase, decrease, atau set',
            'quantity.required' => 'Quantity harus diisi',
            'quantity.integer' => 'Quantity harus berupa angka',
            'quantity.min' => 'Quantity minimal 1',
            'notes.max' => 'Notes maksimal 500 karakter',
        ];
    }
}
