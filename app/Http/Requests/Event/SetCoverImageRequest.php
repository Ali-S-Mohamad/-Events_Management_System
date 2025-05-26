<?php

namespace App\Http\Requests\Event;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class SetCoverImageRequest extends FormRequest
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

        return [
            'image_id' => [
                'required',
                'integer',
                Rule::exists('images', 'id')->where(function ($query) use ($event) {
                    $query->where('imageable_type', get_class($event))
                        ->where('imageable_id', $event->id);
                }),
            ],
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
            'image_id.exists' => 'The selected image does not belong to this event.',
        ];
    }
}