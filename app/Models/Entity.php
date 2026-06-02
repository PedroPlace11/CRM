<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Entity extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name','vat','email','phone','status','owner_id'])
            ->logOnlyDirty()
            ->useLogName('entity');
    }

    protected $fillable = [
        'name', 'vat', 'email', 'phone', 'address', 'status', 'owner_id', 'notes',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function people(): HasMany
    {
        return $this->hasMany(Person::class);
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
