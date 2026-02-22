<?php

namespace Modules\Auth\Http\Requests;

use App\Contracts\ApiFormRequest;

class TestLoginRequest extends ApiFormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => 'required_with:password|string|min:9',
            'password' => 'required_with:phone|string|min:6',
        ];
    }

    public function prepareForValidation()
    {
        if (empty($this->phone) && $this->string('phone'))
            $this->attributes->set('phone' , $this->string('phone'));
        if (empty($this->password) && $this->string('password'))
            $this->attributes->set('password' , $this->string('password'));
    }
}
