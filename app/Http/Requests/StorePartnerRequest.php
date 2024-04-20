<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePartnerRequest extends FormRequest
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
            'partnerName' => 'required|string|max:1000',
            'partnerDetails' => 'required|string|max:1000',
            'createdBy' => 'exists:users,id',
            'banner' => 'nullable|string',
            'logo' => 'nullable|string',
        ];
    }
}
