<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Metadata extends Model
{
    use HasTranslations, UuidAsId, SoftDeletes, SecureDelete;


    const AUTH_PARAMETERS="auth-parameters";
    const REGULATORY_LIMIT = "regulatory-limit";

    const ESCALATION="escalation";

    /**
     * The attributes that are translatable
     *
     * @var array
     */
    public $translatable = ['data'];

    protected $casts = [
        'data' => 'json',
    ];
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
        'name', 'data'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Scope a query to only include metadata of a given name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param $name
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfName($query, $name)
    {
        return $query->where('name', $name);
    }

}
