<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EventRequest extends FormRequest
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
            'createdBy' => $this->user()->id
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:1000',
            'createdBy' => 'exists:users,id',
            'eventTypeId' => 'nullable|string',
            'amount' => 'nullable|string',
            'status' => 'required|boolean',
            'location' => 'required|string',
            'excerpt' => 'nullable|string',
            'description' => 'nullable|string',
            'start' => 'nullable|date|after:tomorrow',
            'end' => 'nullable|date|after:tomorrow',
        ];
    }
}
