<?php

namespace Satis2020\Escalation\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Traits\ActivityTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\SecureForceDeleteWithoutException;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class TreatmentBoard extends Model
{
    use  UuidAsId, LogsActivity, ActivityTrait;

    const STANDARD="standard";
    const SPECIFIC="specific";
    const ACTIVE="active";
    const DISSOLVED="dissolved";
    protected static $logName = 'treatment_board';




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
        'name', 'type','description','created_by','status','institution_id'
    ];



    /**
     * Get the staffs associated with the position
     * @return BelongsToMany
     */
    public function members()
    {
        return $this->belongsToMany(Staff::class);
    }

    /**
     * @return HasOne
     */
    public function claim()
    {
        return $this->hasOne(Claim::class,"treatment_board_id");
    }
}
