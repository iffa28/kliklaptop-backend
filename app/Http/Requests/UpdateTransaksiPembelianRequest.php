<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTransaksiPembelianRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Atur jika cuma admin/customer tertentu
        return true;
    }

    public function rules(): array
    {
        return [
            'metode_pembayaran' => 'sometimes|string|in:Transfer,Bayar Ditempat',
            'status'            => 'sometimes|string|in:transaksi berhasil,transaksi batal,menunggu penjemputan',
            'bukti_pembayaran'  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'metode_pembayaran.in' => 'Metode pembayaran tidak valid.',
            'status.in'            => 'Status tidak valid.',
            'bukti_pembayaran.mimes'=> 'Bukti harus JPG/PNG/PDF.',
        ];
    }
}
