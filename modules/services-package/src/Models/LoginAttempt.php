<?php

namespace Satis2020\ServicePackage\Models;


use Illuminate\Database\Eloquent\Model;
use Satis2020\ServicePackage\Traits\UuidAsId;

class LoginAttempt extends Model
{
    use UuidAsId;

    protected $fillable = ['email',"ip","attempts","last_attempts_at"];

    const UPDATED_AT = null;
}
