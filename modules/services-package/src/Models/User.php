<?php

namespace Satis2020\ServicePackage\Models;

use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticate;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

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
        'username', 'password', 'identite_id', 'disabled_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    public function isEnabled()
    {
        return is_null($this->disabled_at);
    }

    public function identite()
    {
        return $this->belongsTo(Identite::class, 'identite_id');
    }

    /**
     * Find the user instance for the given username.
     *
     * @param  string  $username
     * @return User
     */
    public function findForPassport($username)
    {
        return $this->where('username', $username)->first();
    }

    public function role()
    {
        $role = $this->roles->first();
        return is_null($role) ? null : $role->name;
    }

}
