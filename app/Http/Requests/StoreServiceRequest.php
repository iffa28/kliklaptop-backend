<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // atau bisa diatur untuk autentikasi tertentu
    }

    public function rules(): array
    {
        return [
            'jenis_laptop' => 'required|string|max:255',
            'deskripsi_keluhan' => 'required|string',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ];
    }
}
