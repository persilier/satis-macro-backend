<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Traits\ActivityTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class ReportingTask extends Model
{
    use UuidAsId, SoftDeletes, SecureDelete, LogsActivity, ActivityTrait;

    const BIANNUAL_REPORT="biannual";


    protected static $logName = 'reporting_task';
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['staffs' => 'array'];
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
        'period', 'staffs' , 'institution_id', 'institution_targeted_id','reporting_type'
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institutionTargeted()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function cronTasks()
    {
        return $this->morphMany(CronTask::class, 'model');
    }


    /**
     * @return BelongsToMany
     */
    public function staffs()
    {
        return $this->belongsToMany(Staff::class);
    }


}
