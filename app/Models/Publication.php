<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function authors()
    {
        return $this->hasMany(Author::class);
    }
    public function scopeWhereNotIsArchivedAndIsPublished($query)
    {
        return $query->where('isArchived', false)
            ->where('isPublished', true);
    }
    public function scopeWhereNotIsArchived($query)
    {
        return $query->where('isArchived', false);
    }

    public function bonuses()
    {
        return $this->belongsToMany(Bonus::class, 'publication_bonuses')
            ->withPivot('amount')
            ->withTimestamps();
    }
}
