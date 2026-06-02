<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Entity::class);
    }

    public function rules(): array
    {
        return [
            'name'    => ['required', 'string', 'max:191'],
            'vat'     => ['nullable', 'string', 'max:32'],
            'email'   => ['nullable', 'email', 'max:191'],
            'phone'   => ['nullable', 'string', 'max:32'],
            'address' => ['nullable', 'string', 'max:255'],
            'status'  => ['nullable', 'in:active,inactive,prospect,customer,lost'],
            'notes'   => ['nullable', 'string'],
        ];
    }
}
