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

class Unit extends Model
{
    use HasTranslations, UuidAsId, SoftDeletes, SecureDelete, LogsActivity, ActivityTrait,HasStateTrait;

    protected static $logName = 'unit';

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
    protected $casts = ['name' => 'json', 'description' => 'json', 'others' => 'json'];

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
        'name', 'description', 'unit_type_id', 'institution_id', 'others', 'lead_id', 'parent_id','state_id'
    ];


    /**
     * Get the unitType associated with the unit
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unitType()
    {
        return $this->belongsTo(UnitType::class);
    }

    /**
     * Get the institution associated with the unit
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Get the lead associated with the unit
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lead()
    {
        return $this->belongsTo(Staff::class, 'lead_id');
    }

    /**
     * Get the staffs associated with the unit
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function staffs()
    {
        return $this->hasMany(Staff::class);
    }

    /**
     * Get the unit'children associated with the unit
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(Unit::class, 'parent_id');
    }

    /**
     * Get the unit'parent associated with the unit
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent()
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    /**
     * Get the claims associated with the unit
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function claims()
    {
        return $this->hasMany(Claim::class, 'unit_targeted_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function claimObjects()
    {
        return $this->belongsToMany(ClaimObject::class)->withPivot('institution_id');
    }

    /**
     * Get the treatments for which the unit is responsible
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function treatments()
    {
        return $this->hasMany(Treatment::class, 'responsible_unit_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

}
