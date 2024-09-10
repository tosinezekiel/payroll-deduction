<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'amount',
        'hours_worked',
        'pay_rate',
        'date',
        'external_id',
        'user_id',
        'business_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => to_usd($value),
            set: fn ($value) => to_cents($value),
        );
    }

    protected function payRate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => to_usd($value),
            set: fn ($value) => to_cents($value),
        );
    }
}
