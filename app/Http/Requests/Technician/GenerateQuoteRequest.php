<?php

namespace App\Http\Requests\Technician;

use Illuminate\Foundation\Http\FormRequest;

class GenerateQuoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Completely static rules for Scramble - no dynamic building, no $this->input() calls
        return [
            'items' => 'required|array|min:1',
            'items.0.service_id' => 'required|exists:services,id',
            'items.0.quantity' => 'sometimes|integer|min:1',
            'items.1.service_id' => 'sometimes|exists:services,id',
            'items.1.quantity' => 'sometimes|integer|min:1',
        ];
    }
    
    /**
     * Configure the validator instance for additional runtime validation
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Add additional item validation rules dynamically at runtime
            $items = $this->input('items', []);
            foreach ($items as $index => $item) {
                if ($index > 1) {
                    $validator->addRules([
                        "items.{$index}.service_id" => 'required|exists:services,id',
                        "items.{$index}.quantity" => 'sometimes|integer|min:1',
                    ]);
                }
            }
        });
    }
}
