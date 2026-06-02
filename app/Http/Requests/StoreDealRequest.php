<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDealRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Deal::class);
    }

    public function rules(): array
    {
        return [
            'entity_id'           => ['nullable', 'exists:entities,id'],
            'person_id'           => ['nullable', 'exists:people,id'],
            'stage_id'            => ['required', 'exists:deal_stages,id'],
            'title'               => ['required', 'string', 'max:191'],
            'value'               => ['nullable', 'numeric', 'min:0'],
            'probability'         => ['nullable', 'integer', 'between:0,100'],
            'expected_close_date' => ['nullable', 'date'],
            'source'              => ['nullable', 'string', 'max:64'],
            'notes'               => ['nullable', 'string'],
        ];
    }
}
