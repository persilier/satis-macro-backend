<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Translatable\HasTranslations;

class Staff extends Model
{
    use HasTranslations, UuidAsId, SoftDeletes, SecureDelete;

    /**
     * The attributes that are translatable
     *
     * @var array
     */
    public $translatable = ['others'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['others' => 'json'];

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
        'identite_id', 'position_id', 'unit_id', 'others', 'institution_id'
    ];

    /**
     * Get the lead flag for the staff.
     *
     * @return bool
     */
    public function getIsLeadAttribute()
    {
        return is_null($this->unit)
            ? false
            : $this->unit->lead_id === $this->attributes['id'];
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['is_lead'];

    /**
     * Get the identite associated with the staff
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function identite()
    {
        return $this->belongsTo(Identite::class);
    }

    /**
     * Get the position associated with the staff
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the unit associated with the staff
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Get the institution associated with the staff
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

}
