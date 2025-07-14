<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreServiceByAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id' => 'required|exists:service_requests,id',
            'nama_servis' => 'required|string|max:255',
            'biaya_servis' => 'required|numeric|min:0',
            'bukti_pembayaran' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'service_id.required' => 'Service ID wajib diisi.',
            'service_id.exists' => 'Service ID tidak ditemukan.',
            'nama_servis.required' => 'Nama servis wajib diisi.',
            'biaya_servis.required' => 'Biaya servis wajib diisi.',
            'total_bayar.required' => 'Total bayar wajib diisi.',
            'bukti_pembayaran.file' => 'Bukti pembayaran harus berupa file.',
            'bukti_pembayaran.mimes' => 'Bukti pembayaran harus berupa gambar (jpg, jpeg, png) atau PDF.',
        ];
    }
}
