<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEntityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('entity'));
    }

    public function rules(): array
    {
        return (new StoreEntityRequest)->rules();
    }
}
