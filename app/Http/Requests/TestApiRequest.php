<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use App\Models\Profile;

class TestApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profile' => ['required', 'string', 'max:255'],
            'secret'  => ['required', 'string', 'size:64'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'profile' => $this->query('profile'),
            'secret'  => $this->query('secret'),
        ]);
    }

    public function passedValidation(): void
    {
        $profile = Profile::where('name', $this->profile)
            ->where('secret', $this->secret)
            ->first();

        if (!$profile) {
            throw ValidationException::withMessages([
                'auth' => 'Invalid profile or secret',
            ]);
        }

        $this->merge([
            'profile' => $profile
        ]);
    }
}
