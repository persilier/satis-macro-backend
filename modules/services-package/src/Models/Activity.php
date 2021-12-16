<?php

namespace Satis2020\ServicePackage\Models;

use Spatie\Activitylog\Models\Activity as ActivityLog;
use Satis2020\ServicePackage\Traits\UuidAsId;

class Activity extends ActivityLog
{
    use UuidAsId;

}
