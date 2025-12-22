<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\User
 *
 * Represents a registered user in the system (R01).
 *
 * @property int $id_user
 * @property string $name
 * @property string $email
 * @property string $password_hash
 * @property string|null $location
 * @property string $status 'active', 'blocked', 'deleted'
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * 
 * @property-read \App\Models\Profile|null $profile
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Event[] $events
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Participation[] $participations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Application[] $applications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Invitation[] $invitations
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\SecurityAnswer[] $securityAnswers
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'id_user';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'location',
        'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'id_user';
    }

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * Get the profile associated with the user (R03).
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class, 'id_user', 'id_user');
    }

    /**
     * Get the security answers for the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function securityAnswers(): HasMany
    {
        return $this->hasMany(SecurityAnswer::class, 'id_user', 'id_user');
    }

    /**
     * Get the events organized by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'id_organizer');
    }

    /**
     * Get the participations of the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function participations(): HasMany
    {
        return $this->hasMany(Participation::class, 'id_user');
    }

    /**
     * Get the applications made by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'id_user');
    }

    /**
     * Get the invitations received by the user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(Invitation::class, 'id_invitee');
    }
}
