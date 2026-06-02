<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Person extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name','email','phone','entity_id','position','owner_id'])
            ->logOnlyDirty()
            ->useLogName('person');
    }

    protected $table = 'people';

    protected $fillable = [
        'entity_id', 'owner_id', 'name', 'email', 'phone', 'position', 'notes',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    public function events()
    {
        return $this->morphMany(CalendarEvent::class, 'eventable');
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'subject');
    }
}
