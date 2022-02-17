<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Traits\ActivityTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class SeverityLevel extends Model
{
    use HasTranslations, UuidAsId, SoftDeletes, SecureDelete, LogsActivity, ActivityTrait;

    protected static $logName = 'severity_level';

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
        'name', 'description','status', 'color' , 'others'
    ];

    /**
     * Get the claimObject associated with the claimObject
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function claimObjects()
    {
        return $this->hasMany(ClaimObject::class, 'severity_levels_id', 'id');
    }

}
