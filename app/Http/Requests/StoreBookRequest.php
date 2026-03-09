<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Autorizacion es manejada por las politicas del controlador
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'ISBN' => ['required', 'string', 'max:255', 'unique:books,ISBN'],
            'total_copies' => ['required', 'integer', 'min:0'],
            'available_copies' => ['nullable', 'integer', 'min:0'],
            'is_available' => ['sometimes', 'boolean'],
        ];
    }
}
