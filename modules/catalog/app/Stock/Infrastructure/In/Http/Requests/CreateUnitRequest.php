<?php

namespace App\Stock\Infrastructure\In\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|string',
            'code' => 'required|string',
            'name' => 'required|string',
        ];
    }
}
