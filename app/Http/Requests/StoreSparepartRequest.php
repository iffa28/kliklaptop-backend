<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSparepartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'nama_sparepart' => 'required|string|max:255|unique:spareparts,nama_sparepart',
            'harga_satuan' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_sparepart.required' => 'Nama sparepart wajib diisi.',
            'nama_sparepart.unique' => 'Sparepart dengan nama ini sudah ada.',
            'harga_satuan.required' => 'Harga satuan wajib diisi.',
            'harga_satuan.numeric' => 'Harga satuan harus berupa angka.',
            'harga_satuan.min' => 'Harga satuan tidak boleh negatif.',
        ];
    }
}
