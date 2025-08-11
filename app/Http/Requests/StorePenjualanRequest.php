<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePenjualanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        // return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // 'no_bukti' => 'required|string|max:255',
            'tgl_bukti' => 'required|date',
            'nama_pelanggan' => 'required|exists:pelanggans,id',
            'barang' => 'required|array',
            'barang.*.nama_barang' => 'required|string|max:255',
            'barang.*.qty' => 'required|integer|min:1',
            'barang.*.harga' => 'required|numeric|min:1',
        ];
    }
}
