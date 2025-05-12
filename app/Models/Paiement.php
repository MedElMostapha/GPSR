<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    protected $fillable = [
        'user_id',
        'publication_id',
        'montant',
        'name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publication(): BelongsTo
    {
        return $this->belongsTo(Publication::class);
    }

    public static function getPaiementsGroupedByUser()
    {
        $paiements = self::with(['user.roles']) // Load user and roles relationships
            ->selectRaw('user_id, SUM(montant) as total_prix')
            ->groupBy('user_id')
            ->orderByDesc('total_prix') // Order by highest total payments
            ->get()
            ->map(function ($paiement) {
                return [
                    'user_name' => $paiement->user->name,
                    'user_email' => $paiement->user->email,
                    'role_name' => $paiement->user->roles->pluck('name')->implode(', '), // Handle multiple roles
                    'total_prix' => $paiement->total_prix,
                ];
            });

        // Calculate total of all payments
        $total_paiement = $paiements->sum('total_prix');

        return [
            'paiements_grouped' => $paiements,
            'total_paiement' => $total_paiement,
        ];
    }
}
