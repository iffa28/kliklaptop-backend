<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceSparepartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_by_admin_id' => 'required|exists:service_by_admin,id',
            'sparepart_id' => 'required|exists:spareparts,id',
        ];
    }

    public function messages(): array
    {
        return [
            'service_by_admin_id.required' => 'ID service oleh admin wajib diisi.',
            'service_by_admin_id.exists' => 'ID service oleh admin tidak ditemukan.',
            'sparepart_id.required' => 'ID sparepart wajib diisi.',
            'sparepart_id.exists' => 'ID sparepart tidak ditemukan.',
        ];
    }
}
