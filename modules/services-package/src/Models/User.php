<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Hash;
use League\OAuth2\Server\Exception\OAuthServerException;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

/**
 * Class User
 * @package Satis2020\ServicePackage\Models
 */
class User extends Authenticate
{
    const VERIFIED_USER = '1';
    const UNVERIFIED_USER = '0';
    use Notifiable, HasApiTokens, UuidAsId, SoftDeletes, SecureDelete, HasRoles;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at', 'disabled_at'];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'password', 'identite_id', 'password_updated_at', 'disabled_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * @var string
     */
    protected $guard_name = 'api';


    /**
     * @return bool
     */
    public function isEnabled()
    {
        return is_null($this->disabled_at);
    }

    /**
     * @return BelongsTo
     */
    public function identite()
    {
        return $this->belongsTo(Identite::class, 'identite_id');
    }


    /**
     * Find the user instance for the given username.
     *
     * @param string $username
     * @return User
     * @throws OAuthServerException
     */
    public function findForPassport($username)
    {
        $user = $this->where('username', $username)->first();

        if($user !== null && $user->disabled_at !== NULL) {
            throw new OAuthServerException('User account is not activated', 6, 'account_inactive', 401);
        }

        return $user;
    }


    /**
     * @return |null
     */
    public function role()
    {
        $role = $this->roles->first();
        return is_null($role) ? null : $role->name;
    }


    public function routeNotificationForMail($notification)
    {
        // Return name and email address...
        return $this->username;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function historyPasswords()
    {
        return $this->hasMany(HistoryPassword::class);
    }

}
