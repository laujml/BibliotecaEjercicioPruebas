<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // autorizacion es manejada por las politicas del controlador
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $bookId = $this->route('book')->id;

        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'ISBN' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('books', 'ISBN')->ignore($bookId),
            ],
            'total_copies' => ['sometimes', 'integer', 'min:0'],
            'available_copies' => ['sometimes', 'integer', 'min:0'],
            'is_available' => ['sometimes', 'boolean'],
        ];
    }
}
