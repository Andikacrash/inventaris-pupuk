<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleRequest extends FormRequest
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
            // customer_name optional (POS boleh kosong / walk-in)
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'delivery_method' => 'nullable|in:pickup,delivery',
            'delivery_address' => 'nullable|string|max:500',
            'delivery_phone' => 'nullable|string|max:20',
            'delivery_level' => 'nullable|in:hemat,reguler,express',
            'delivery_distance_km' => 'nullable|numeric|min:0',
            'shipping_fee' => 'nullable|numeric|min:0',
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
            'notes' => 'nullable|string|max:1000',
            'total_amount' => 'required|numeric|min:0',
            // tambahkan opsi 'credit' untuk menyimpan sebagai piutang jika pembayaran kurang
            'payment_method' => 'required|in:cash,transfer,card,credit,qris,debit',
            // optional discount (nominal rupiah)
            'discount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'payment' => 'required|numeric|min:0',
            // change is computed server-side, no need to require it from client
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'Nama customer harus diisi',
            'total_amount.required' => 'Total amount harus diisi',
            'payment_method.required' => 'Metode pembayaran harus dipilih',
            'items.required' => 'Items harus diisi',
            'items.min' => 'Minimal 1 item harus dipilih',
            'items.*.product_id.required' => 'Product ID harus diisi',
            'items.*.product_id.exists' => 'Product tidak ditemukan',
            'items.*.quantity.required' => 'Quantity harus diisi',
            'items.*.quantity.min' => 'Quantity minimal 1',
            'items.*.unit_price.required' => 'Unit price harus diisi',
            'items.*.unit_price.min' => 'Unit price tidak boleh negatif',
            'payment.required' => 'Payment harus diisi',
            'payment.min' => 'Payment tidak boleh negatif',
            'change.required' => 'Change harus diisi',
            'change.numeric' => 'Change harus berupa angka',
        ];
    }
}
