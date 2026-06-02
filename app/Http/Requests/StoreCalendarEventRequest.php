<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCalendarEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\CalendarEvent::class);
    }

    public function rules(): array
    {
        return [
            'eventable_type' => ['nullable', 'in:entity,person,deal'],
            'eventable_id'   => ['nullable', 'integer'],
            'title'          => ['required', 'string', 'max:191'],
            'description'    => ['nullable', 'string'],
            'type'           => ['nullable', 'in:meeting,task,call,note'],
            'start_at'       => ['required', 'date'],
            'end_at'         => ['nullable', 'date', 'after_or_equal:start_at'],
            'location'       => ['nullable', 'string', 'max:191'],
            'priority'       => ['nullable', 'in:low,normal,high'],
            'reminder_at'    => ['nullable', 'date'],
        ];
    }
}
