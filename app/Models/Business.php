<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Business extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'external_id',
        'enabled',
        'deduction_percentage'
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('external_id')->using(BusinessUser::class);
    }

    protected function deductionPercentage(): Attribute
    {
        return Attribute::make(
            set: fn ($value) => $value === null ? 30 : $value,
            get: fn ($value) => $value === null ? 30 : $value
        );
    }
}
