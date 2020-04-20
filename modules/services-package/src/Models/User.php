<?php

namespace Satis2020\ServicePackage\Models;

use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, UuidAsId, SoftDeletes, SecureDelete, HasRoles;

    const VERIFIED_USER = '1';
    const UNVERIFIED_USER = '0';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

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
        'username', 'password', 'verified', 'verification_token', 'identite_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'verification_token',
    ];

    public function isVerified()
    {
        return $this->verified == User::VERIFIED_USER;
    }

    public static function generateVerificationToken()
    {
        return Str::random(40);
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

    public function lastname()
    {
        return $this->identite->lastname;
    }

    public function firstname()
    {
        return $this->identite->firstname;
    }

    public function email()
    {
        return $this->identite->email;
    }

    public function role()
    {
        $role = $this->roles->first();
        return is_null($role) ? null : $role->name;
    }

}
