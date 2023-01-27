<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Traits\ActivityTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\SecureForceDeleteWithoutException;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class Position extends Model
{
    use HasTranslations, UuidAsId, SoftDeletes, SecureDelete, SecureForceDeleteWithoutException, LogsActivity, ActivityTrait;

    protected static $logName = 'position';

    /**
     * The attributes that are translatable
     *
     * @var array
     */
    public $translatable = ['name', 'description', 'others'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['name' => 'json', 'description'=> 'json', 'others'=> 'json'];

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
        'name', 'description', 'others'
    ];

    /**
     * Get the institutions associated with the position
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function institutions()
    {
        return $this->belongsToMany(Institution::class);
    }

    /**
     * Get the staffs associated with the position
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function staffs()
    {
        return $this->hasMany(Staff::class);
    }

}
