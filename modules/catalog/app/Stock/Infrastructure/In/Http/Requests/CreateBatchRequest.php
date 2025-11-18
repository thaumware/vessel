<?php

namespace App\Stock\Infrastructure\In\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => 'required|string',
            'sku' => 'required|string',
            'location_id' => 'required|string',
            'quantity' => 'required|integer',
            'lot_number' => 'nullable|string',
        ];
    }
}
