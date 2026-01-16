<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Completely static rules - no dynamic building
        return [
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
            'aspects' => 'nullable|array',
            'aspects.quality' => 'nullable|integer|min:1|max:5',
            'aspects.speed' => 'nullable|integer|min:1|max:5',
            'aspects.communication' => 'nullable|integer|min:1|max:5',
            'aspects.professionalism' => 'nullable|integer|min:1|max:5',
        ];
    }
}
