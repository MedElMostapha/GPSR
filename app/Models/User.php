<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Publication;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;


    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function publications()
    {
        return $this->hasMany(Publication::class);
    }
    public function numberOfPublicationsPublished()
    {
        return $this->publications()->where('isPublished', true)->count();
    }
    public function mobilites()
    {
        return $this->hasMany(Mobilite::class);
    }
    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Define the deleting event
        static::deleting(function ($user) {
            // Delete all related publications
            $user->publications()->delete();

            // Delete all related mobilities
            $user->mobilites()->delete();

            // If there are other relationships, delete them here
        });
    }
}
