<?php

namespace Satis2020\ServicePackage\Models;

use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Traits\UuidAsId;

class InactivityReactivationHistory extends Model
{
    use UuidAsId;

    protected $fillable = [
        'user_id',
        'action'
    ];

    const DEACTIVATION = "deactivation";
    const ACTIVATION = "activation";

}
