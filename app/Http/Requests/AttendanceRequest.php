<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Attendance;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'role_id' => 'nullable|exists:roles,id',
            'date' => 'required|date',
            'time_in' => 'required|date_format:H:i:s',
            'time_out' => 'nullable|date_format:H:i:s|after:time_in',
            'notes' => 'nullable|string|max:500',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Check if user already has an attendance record for this date
            $exists = Attendance::where('user_id', $this->user_id)
                ->whereDate('date', $this->date)
                ->when($this->route('attendance'), function ($query) {
                    // Exclude current record when updating
                    $query->where('id', '!=', $this->route('attendance')->id);
                })
                ->exists();

            if ($exists) {
                $validator->errors()->add(
                    'user_id',
                    'This user already has an attendance record for this date.'
                );
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Please select a user.',
            'user_id.exists' => 'The selected user does not exist.',
            'date.required' => 'Date is required.',
            'time_in.required' => 'Check-in time is required.',
            'time_out.after' => 'Check-out time must be after check-in time.',
        ];
    }
}
