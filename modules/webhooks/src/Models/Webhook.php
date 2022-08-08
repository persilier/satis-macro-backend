<?php

namespace Satis2020\Webhooks\Models;

use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Traits\UuidAsId;

class Webhook extends Model
{
    use  UuidAsId;

    const STANDARD="standard";
    const SPECIFIC="specific";
    const ACTIVE="active";
    const DISSOLVED="dissolved";
    protected static $logName = 'treatment_board';




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
        'name', 'event','url','institution_id'
    ];

}
