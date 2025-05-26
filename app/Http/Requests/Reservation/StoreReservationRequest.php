<?php

namespace App\Http\Requests\Reservation;

use App\Models\Event;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => [
                'required',
                'exists:events,id',
                function ($attribute, $value, $fail) {
                    $event = Event::find($value);
                    if ($event && $event->ends_at < now()) {
                        $fail('لا يمكن الحجز في فعالية منتهية.');
                    }
                },
            ],
            'guests_count' => 'required|integer|min:1|max:10',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'event_id.required' => 'يجب تحديد الفعالية',
            'event_id.exists' => 'الفعالية المحددة غير موجودة',
            'guests_count.required' => 'يجب تحديد عدد الضيوف',
            'guests_count.integer' => 'عدد الضيوف يجب أن يكون رقمًا صحيحًا',
            'guests_count.min' => 'عدد الضيوف يجب أن يكون على الأقل 1',
            'guests_count.max' => 'عدد الضيوف يجب أن لا يتجاوز 10',
        ];
    }
}
