<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mobilite extends Model
{
    use HasFactory;

    protected $fillable = [
        'labo_accueil',
        'rapport_mobilite',
        'type',
        'ville',
        'pays',
        'isValidated',
    ];
}
?>