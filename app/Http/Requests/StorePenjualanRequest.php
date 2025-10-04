<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class StorePenjualanRequest
 *
 * This class defines the validation rules and authorization for storing and
 * updating a sales transaction. It is used to ensure that the incoming data
 * for a sale is valid before it is processed by the controller.
 *
 * @package App\Http\Requests
 */
class StorePenjualanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * This method can be used to implement authorization logic. For this application,
     * we are allowing all authenticated users to make this request, so it returns true.
     *
     * @return bool True if the user is authorized, false otherwise.
     */
    public function authorize(): bool
    {
        return true;
        // return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * This method returns an array of validation rules that are applied to the
     * incoming request data when creating or updating a sales record. It validates
     * the transaction date, customer, and the array of items.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     *         An array of validation rules.
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