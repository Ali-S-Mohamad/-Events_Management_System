<?php

namespace App\Http\Requests\Reservation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // التحقق من أن المستخدم هو صاحب الحجز أو مسؤول
        $reservation = $this->route('reservation');
        return Auth::check() && (Auth::id() === $reservation->user_id || Auth::user()->hasRole('admin'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
            'guests_count.required' => 'يجب تحديد عدد الضيوف',
            'guests_count.integer' => 'عدد الضيوف يجب أن يكون رقمًا صحيحًا',
            'guests_count.min' => 'عدد الضيوف يجب أن يكون على الأقل 1',
            'guests_count.max' => 'عدد الضيوف يجب أن لا يتجاوز 10',
        ];
    }
}
