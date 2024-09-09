<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'external_id',
        'enabled',
        'deduction_percentage'
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('external_id')->using(BusinessUser::class);
    }

    protected function deductionPercentage(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => is_null($value) ?? 30
        );
    }
}
