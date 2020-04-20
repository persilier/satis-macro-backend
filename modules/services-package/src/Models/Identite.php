<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;

class Identite extends Model
{
    use UuidAsId, SoftDeletes, SecureDelete;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['other_attributes' => 'json'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'firstname', 'lastname', 'sexe', 'telephone', 'email', 'other_attributes'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function user()
    {
        return $this->hasOne(User::class, 'identite_id');
    }


}
