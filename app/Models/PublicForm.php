<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PublicForm extends Model
{
    protected $fillable = [
        'name', 'slug', 'fields', 'success_message',
        'captcha_required', 'owner_id', 'active',
    ];

    protected $casts = [
        'fields' => 'array',
        'captcha_required' => 'bool',
        'active' => 'bool',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(LeadSubmission::class);
    }
}
