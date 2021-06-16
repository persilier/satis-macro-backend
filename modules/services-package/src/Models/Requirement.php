<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Translatable\HasTranslations;

class Requirement extends Model
{
    use HasTranslations, UuidAsId, SoftDeletes, SecureDelete;

    /**
     * The attributes that are translatable
     *
     * @var array
     */
    public $translatable = ['description'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['description'=> 'json'];

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
    protected $fillable = ['name', 'description'];

    /**
     * Get the claimObjects associated with the requirement
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function claimObjects()
    {
        return $this->belongsToMany(ClaimObject::class);
    }

}
