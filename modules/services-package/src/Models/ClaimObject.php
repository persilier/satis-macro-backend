<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Traits\ActivityTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class ClaimObject extends Model
{
    use HasTranslations, UuidAsId, SoftDeletes, SecureDelete, LogsActivity, ActivityTrait;

    protected static $logName = 'claim_object';

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
        'name', 
        'description',
         'time_limit', 
         'severity_levels_id', 
         'claim_category_id', 
         'others',
         'time_unit',
        'time_staff',
        'time_treatment',
        'time_validation',
        'time_measure_satisfaction',
        'internal_control'
    ];

    /**
     * Get the claimCategory associated with the claimObject
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function claimCategory()
    {
        return $this->belongsTo(ClaimCategory::class);
    }

    /**
     * Get the severityLevel associated with the severityLevel
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function severityLevel()
    {
        return $this->belongsTo(SeverityLevel::class, 'severity_levels_id', 'id');
    }

    /**
     * Get the requirements associated with the claimObject
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function requirements()
    {
        return $this->belongsToMany(Requirement::class);
    }

    /**
     * Get the claims associated with the claimObject
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function claims()
    {
        return $this->hasMany(Claim::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function units()
    {
        return $this->belongsToMany(Unit::class)->withPivot('institution_id');
    }


}
