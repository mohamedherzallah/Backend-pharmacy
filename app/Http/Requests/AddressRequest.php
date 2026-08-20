<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
              'title' => 'nullable|string|max:50',
            'area' => 'required|string|max:100',
            'street' => 'required|string|max:100',
            'building_number' => 'nullable|string|max:20',
            'floor' => 'nullable|string|max:20',
            'apartment' => 'nullable|string|max:20',
            'additional_info' => 'nullable|string|max:255',
            'is_default' => 'boolean',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ];
    }
}
