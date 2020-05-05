<?php

namespace Satis2020\ServicePackage\Models;

use Satis2020\ServicePackage\Models\ClaimObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Translatable\HasTranslations;

class ClaimCategory extends Model
{
    use HasTranslations, UuidAsId, SoftDeletes, SecureDelete;

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
        'name', 'description', 'time_limit', 'severity_levels_id', 'others'
    ];

    /**
     * Get the claimObjects associated with the claimCategory
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function claimObjects()
    {
        return $this->hasMany(ClaimObject::class);
    }

    /**
     * Get the severityLevel associated with the severityLevel
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function severityLevel()
    {
        return $this->belongsTo(SeverityLevel::class, 'severity_levels_id', 'id');
    }
}
