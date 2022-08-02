<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Translatable\HasTranslations;

class MessageApi extends Model
{
    use HasTranslations, UuidAsId, SoftDeletes, SecureDelete;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are translatable
     *
     * @var array
     */
    public $translatable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['params' => 'array'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    public $fillable = ['name', 'method', 'params'];

    /**
     * Get the institutionMessageApis records associated with the messageApi.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function institutionMessageApi()
    {
        return $this->hasMany(InstitutionMessageApi::class);
    }

}
