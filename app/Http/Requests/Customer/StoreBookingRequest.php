<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Completely static rules for Scramble - no dynamic building, no $this->input() calls
        return [
            'device_type' => 'required|in:laptop,phone,ac,fridge,tablet,desktop,other',
            'brand' => 'required|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'issue_description' => 'required|string|min:10',
            'address' => 'nullable|string',
            'preferred_date' => 'nullable|date|after_or_equal:today',
            'preferred_time' => 'nullable|string',
            'photos' => 'nullable|array',
            'photos.0' => 'nullable|image|max:5120',
            'photos.1' => 'nullable|image|max:5120',
            'photos.2' => 'nullable|image|max:5120',
        ];
    }
    
    /**
     * Configure the validator instance for additional runtime validation
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Add additional photo validation rules dynamically at runtime
            if ($this->hasFile('photos')) {
                $photos = $this->file('photos');
                foreach ($photos as $index => $photo) {
                    if ($index > 2) {
                        $validator->addRules(["photos.{$index}" => 'image|max:5120']);
                    }
                }
            }
        });
    }
}
