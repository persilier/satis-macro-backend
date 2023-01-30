<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis\CountriesPackage\Models\State;
use Satis\CountriesPackage\Traits\HasStateTrait;
use Satis2020\ServicePackage\Traits\ActivityTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Translatable\HasTranslations;

class ConfigurationActivePilot extends Model
{
    use UuidAsId, SoftDeletes, SecureDelete, LogsActivity, ActivityTrait,HasStateTrait;

    protected static $logName = 'configuration_active_pilots';



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
        'many_active_pilot', 'institution_id'
    ];



    /**
     * Get the institution associated with the unit
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }


}
