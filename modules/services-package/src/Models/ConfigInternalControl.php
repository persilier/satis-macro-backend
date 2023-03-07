<?php


namespace Satis2020\ServicePackage\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Satis\CountriesPackage\Traits\HasStateTrait;
use Satis2020\ServicePackage\Traits\ActivityTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\UuidAsId;
use Spatie\Activitylog\Traits\LogsActivity;

class ConfigInternalControl extends Model
{
    use UuidAsId, SoftDeletes, SecureDelete, LogsActivity, ActivityTrait,HasStateTrait;

    protected $guarded = [];
}
