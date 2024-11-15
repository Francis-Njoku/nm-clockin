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
            'is_approved' => 'nullable|boolean',
            'approved_by' => 'nullable|exists:users,id',
            'start' => 'required|date|before_or_equal:end',
            'end' => 'required|date|after_or_equal:start',
            'status' => 'required|string|in:pending,approved,rejected',
            'leave_type' => 'required|string|max:50',
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
        ];
    }

}
