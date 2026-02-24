<?php

namespace Modules\Auth\Http\Requests;

use App\Contracts\ApiFormRequest;
use Illuminate\Support\Facades\Hash;

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

    protected function prepareForValidation(): void
    {
        if (!$this->has('phone') && $this->attributes->has('profile')) {
            $profile = $this->attributes->get('profile');
            $this->merge(['phone' => $profile->user->phone]);
        }

        if (!$this->has('password') && $this->attributes->has('profile')) {
            $profile = $this->attributes->get('profile');
            $this->merge(['password' => $profile->user->password]);
        }
    }
}
