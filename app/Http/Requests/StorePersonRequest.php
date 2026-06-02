<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Person::class);
    }

    public function rules(): array
    {
        return [
            'entity_id' => ['nullable', 'exists:entities,id'],
            'name'      => ['required', 'string', 'max:191'],
            'email'     => ['nullable', 'email', 'max:191'],
            'phone'     => ['nullable', 'string', 'max:32'],
            'position'  => ['nullable', 'string', 'max:191'],
            'notes'     => ['nullable', 'string'],
        ];
    }
}
