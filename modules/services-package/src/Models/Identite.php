<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;

class Identite extends Model
{
    use UuidAsId, SoftDeletes, SecureDelete, Notifiable;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['email' => 'array', 'telephone' => 'array', 'id_card' => 'array', 'other_attributes' => 'json',];

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
        'firstname', 'lastname', 'raison_sociale',  'type_client',  'sexe', 'telephone', 'email', 'ville', 'id_card', 'other_attributes'
    ];

    protected $appends = [

        'fullName'
    ];


    public function getfullNameAttribute()
    {
        $fullName = $this->raison_sociale != null ? $this->raison_sociale : $this->firstname . " " . $this->lastname;
        return $fullName;
    }

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

    /**
     * Get the staff associated with the identite
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    public function client()
    {
        return $this->hasOne(Client::class, 'identites_id');
    }

    /**
     * Get the claims associated with the identite
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function claims()
    {
        return $this->hasMany(Claim::class, 'claimer_id');
    }

    /**
     * Route notifications for the mail channel.
     *
     * @param \Illuminate\Notifications\Notification $notification
     * @return array|string
     */
    public function routeNotificationForMail($notification)
    {
        // Return name and email address...
        return $this->email[0];
    }
}
