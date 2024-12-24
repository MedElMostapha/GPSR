<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $fillable = ['criteria', 'bonus_amount'];

    public function publications()
    {
        return $this->belongsToMany(Publication::class, 'publication_bonuses')
            ->withPivot('amount')
            ->withTimestamps();
    }
}
