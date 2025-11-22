<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// Import Eloquent relationship classes.
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\Profile;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Tell Laravel which database table this model uses.
    // Our table is called 'user' instead of the default 'users'.    
    protected $table = 'user';

    // Tell Laravel which column is the primary key in the 'user' table.
    // Here it is 'id_user' instead of the default 'id'.    
    protected $primaryKey = 'id_user';    

    /*
    // If we uncomment this, Laravel will NOT automatically manage
    // the created_at and updated_at columns.
    public $timestamps  = false;
    */

    /**
     * The attributes that are mass assignable.
     *
     * These are the fields we allow Laravel to fill in automatically
     * when we do User::create([...]) or $user->update([...]).
     * This helps protect us from accidentally writing to fields
     * we did not intend to change.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'location',
        'status'
    ];

    /**
     * The attributes that should be hidden when the model is converted
     * to an array or JSON.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to a specific type.
     * Here we tell Laravel to always hash the password_hash field
     * when it is set.     
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Add casts here if needed (e.g. dates).
        ];
    }

    public function getRouteKeyName()
    {
        return 'id_user';
    }

    /*
    public function cards(): HasMany
    {
        return $this->hasMany(Card::class);
    }
    */

    /**
     * Tell Laravel which field should be used as the "password"
     * for authentication.
     *
     * By default Laravel expects a 'password' column,
     * but in our database the column is called 'password_hash',
     * so we return that instead.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // R03 profile (1:1) per ER/EBD
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'id_user', 'id_user');
    }

}
