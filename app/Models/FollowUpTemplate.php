<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUpTemplate extends Model
{
    protected $fillable = ['name', 'subject', 'body', 'active'];

    protected $casts = ['active' => 'bool'];
}
