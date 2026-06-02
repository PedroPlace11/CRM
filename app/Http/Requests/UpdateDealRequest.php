<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('deal'));
    }

    public function rules(): array
    {
        return (new StoreDealRequest)->rules();
    }
}
