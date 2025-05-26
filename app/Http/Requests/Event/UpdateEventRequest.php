<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $event = $this->route('event');
        return Auth::check() && (Auth::user()->hasRole('admin') || Auth::id() === $event->user_id);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $event = $this->route('event');
        $startDateRule = $event->isPast() ? 'required|date' : 'required|date|after_or_equal:today';

        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'event_type_id' => 'sometimes|required|exists:event_types,id',
            'location_id' => 'sometimes|required|exists:locations,id',
            'starts_at' => $startDateRule,
            'ends_at' => 'sometimes|required|date|after:starts_at',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_cover' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'starts_at.after_or_equal' => 'The event start date must be today or in the future.',
            'ends_at.after' => 'The event end date must be after the start date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If only one date is provided, keep the other one unchanged
        if ($this->has('starts_at') && !$this->has('ends_at')) {
            $event = $this->route('event');
            $this->merge([
                'ends_at' => $event->ends_at,
            ]);
        }
    }
}