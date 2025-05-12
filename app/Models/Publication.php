<?php

namespace App\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Publication extends Model
{
    use HasFactory;
    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paiement(): HasOne
    {
        return $this->hasOne(Paiement::class);
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


    public static function getMonthlyPublicationStatistics($year)
    {
        $y = DateTime::createFromFormat('Y', $year)->format('Y');

        return self::whereYear('publication_date', $y)
            ->where('isPublished', true)
            ->selectRaw("DATE_FORMAT(publication_date, '%b') as month, COUNT(*) as count, MIN(publication_date) as min_date")
            ->groupBy('month')
            ->orderBy('min_date')
            ->pluck('count', 'month');
    }
}
