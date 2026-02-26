<?php

namespace App\Http\Requests;

use App\Contracts\ApiFormRequest;
use Illuminate\Validation\Rule;

class RunAllTestRequest extends ApiFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $validGroups = ['auth'];

        return [
            'group' => ['nullable', 'array', 'min:1'],
            'group.*' => ['string', Rule::in($validGroups)],
        ];
    }
    protected function prepareForValidation(): void
    {
        $group = $this->input('group' , $this->query('group'));

        if (is_string($group)) {
            $group = explode(',', $group);
        }

        if (!empty($group)) {
            $this->merge(['group' => $group]);
        }
    }
}
