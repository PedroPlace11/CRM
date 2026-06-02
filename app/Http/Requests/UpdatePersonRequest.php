<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('person'));
    }

    public function rules(): array
    {
        return (new StorePersonRequest)->rules();
    }
}
