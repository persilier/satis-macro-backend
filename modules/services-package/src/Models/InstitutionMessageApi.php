<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Translatable\HasTranslations;

class InstitutionMessageApi extends Model
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
    public $fillable = ['institution_id', 'message_api_id', 'params'];

    /**
     * Get the institution that owns the institutionMessageApi.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    /**
     * Get the messageApi that owns the institutionMessageApi.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function messageApi()
    {
        return $this->belongsTo(MessageApi::class);
    }

}
