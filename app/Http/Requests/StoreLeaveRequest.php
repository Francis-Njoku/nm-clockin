<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLeaveRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }
    protected function prepareForValidation()
    {
        \Log::info('Incoming request data:', $this->all());
        $this->merge([
            'user_id' => $this->user()->id
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'name' => 'required|string|max:255',
            'approved_by' => 'nullable|exists:users,id',
            'start' => 'required|date|before_or_equal:end',
            'end' => 'required|date|after_or_equal:start',
            'status' => 'required|string|in:pending,approved,rejected',
            'leave_type' => 'required|string|max:50',
            'user_recipients' => 'nullable|string', // Validate as an optional string
            'reason' => 'nullable|string|max:1000', // Add validation for reason
            'file_attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // Add validation for file attachment
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'The user field is required.',
            'user_id.exists' => 'The selected user does not exist.',
            'name.required' => 'The name field is required.',
            'start.required' => 'The start date is required.',
            'end.required' => 'The end date is required.',
            'status.required' => 'The status field is required.',
            'leave_type.required' => 'The leave type is required.',
            'start.before_or_equal' => 'The start date must be before or equal to the end date.',
            'end.after_or_equal' => 'The end date must be after or equal to the start date.',
            'user_recipients.string' => 'The user recipients field must be a valid string.',
            'reason.max' => 'The reason must not exceed 1000 characters.',
            'file_attachment.file' => 'The attachment must be a valid file.',
            'file_attachment.mimes' => 'The file must be a type of jpg, jpeg, png, or pdf.',
            'file_attachment.max' => 'The file size must not exceed 2MB.',
        ];
    }

}
