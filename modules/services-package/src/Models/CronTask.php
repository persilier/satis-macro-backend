<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Translatable\HasTranslations;

/**
 * Class CronTask
 * @package Satis2020\ServicePackage\Models
 */
class CronTask extends Model
{
    use UuidAsId, SoftDeletes, SecureDelete;

    protected $primaryKey = 'cron_id';
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

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
    protected $fillable = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function cron()
    {
        return $this->morphTo();
    }


}
