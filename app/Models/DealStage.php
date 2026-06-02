<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DealStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'position', 'color', 'is_won', 'is_lost', 'is_follow_up',
    ];

    protected $casts = [
        'is_won' => 'bool',
        'is_lost' => 'bool',
        'is_follow_up' => 'bool',
        'position' => 'int',
    ];

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class, 'stage_id');
    }
}
