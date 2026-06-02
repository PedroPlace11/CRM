<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Deal extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title','value','stage_id','probability','expected_close_date','owner_id'])
            ->logOnlyDirty()
            ->useLogName('deal');
    }

    protected $fillable = [
        'entity_id', 'person_id', 'owner_id', 'stage_id',
        'title', 'value', 'probability', 'expected_close_date',
        'source', 'notes', 'last_activity_at',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'probability' => 'int',
        'expected_close_date' => 'date',
        'last_activity_at' => 'datetime',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(Entity::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function stage(): BelongsTo
    {
        return $this->belongsTo(DealStage::class, 'stage_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'deal_products')
            ->withPivot(['quantity', 'unit_price', 'discount'])
            ->withTimestamps();
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function emails(): HasMany
    {
        return $this->hasMany(DealEmail::class);
    }

    public function followUpSequence(): HasOne
    {
        return $this->hasOne(FollowUpSequence::class);
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
