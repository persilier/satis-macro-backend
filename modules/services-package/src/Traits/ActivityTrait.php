<?php

namespace Satis2020\ServicePackage\Traits;

use Illuminate\Database\Eloquent\HigherOrderBuilderProxy;
use Illuminate\Support\HigherOrderCollectionProxy;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Spatie\Activitylog\Contracts\Activity;

trait ActivityTrait
{
    use DataUserNature;
    /***
     * @param Activity $activity
     * @param string $eventName
     * @throws RetrieveDataUserNatureException
     */
    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->ip_address = request()->ip();
        $activity->description = "This model {$this->getLogNameToUse()} has been {$eventName}";
        $activity->institution_id = $this->getInstitutionId();
        $activity->log_action = strtoupper($eventName);
    }

    /**
     * @return HigherOrderBuilderProxy|HigherOrderCollectionProxy|mixed|null
     * @throws RetrieveDataUserNatureException
     */
    protected function getInstitutionId()
    {
        if (auth()->user()) {
            return $staff = $this->user()->load('identite.staff')->identite->staff->institution_id;
        }

        return null;
    }

}
