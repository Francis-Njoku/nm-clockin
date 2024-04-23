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
            'name' => 'required|string',
            'createdBy' => 'exists:users,id',
            'eventTypeId' => 'required|string',
            'amount' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'status' => 'required|boolean',
            'location' => 'required|string',
            'excerpt' => 'required|string',
            'quantity' => 'required|string',
            'registration' => 'required|string',
            'description' => 'required|string',
            'start' => 'required|date|after:tomorrow',
            'end' => 'required|date|after:tomorrow',
        ];
    }
}
