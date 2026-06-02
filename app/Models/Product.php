<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'sku', 'unit_price', 'description', 'active'];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'active' => 'bool',
    ];

    public function deals(): BelongsToMany
    {
        return $this->belongsToMany(Deal::class, 'deal_products')
            ->withPivot(['quantity', 'unit_price', 'discount'])
            ->withTimestamps();
    }
}
