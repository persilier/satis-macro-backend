<?php


namespace Satis2020\ServicePackage\Traits;


use Carbon\Carbon;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\ClaimObject;

trait Dashboard
{

    protected function getRelations()
    {
        return [
            'claimObject.claimCategory', 'claimer', 'relationship', 'accountTargeted', 'institutionTargeted', 'unitTargeted', 'requestChannel',
            'responseChannel', 'amountCurrency', 'createdBy.identite', 'completedBy.identite', 'files', 'activeTreatment'
        ];
    }

    protected function getActiveTreatmentRelations()
    {
        return [
            'responsibleUnit', 'assignedToStaffBy.identite', 'responsibleStaff.identite', 'satisfactionMeasuredBy.identite'
        ];
    }

    protected function getDataCollectionMonthly($subKeys)
    {
        $months = [];
        $statisticsCollection = collect([]);

        $date = Carbon::now()->subMonths(12);

        $date->settings(['monthOverflow' => false,]);

        for ($i = 1; $i <= 12; $i++) {
            $date->addMonths(1);
            $statisticsCollection->put($this->formatMontWithYear($date), $subKeys);
        }

        return $statisticsCollection;
    }

    protected function getStatisticsKeys()
    {
        return $keys = [
            'totalRegistered', 'totalIncomplete', 'totalComplete', 'totalTransferredToUnit', 'totalBeingProcess', 'totalTreated',
            'totalUnfounded', 'totalMeasuredSatisfaction'
        ];
    }

    public function getDataCollection($keys, $permissions)
    {
        $subKeysPermissions = [
            'show-dashboard-data-all-institution' => 'allInstitution',
            'show-dashboard-data-my-institution' => 'myInstitution',
            'show-dashboard-data-my-unit' => 'myUnit',
            'show-dashboard-data-my-activity' => 'myActivity'
        ];

        $subKeys = [];

        foreach ($subKeysPermissions as $permission => $subKey) {
            if ($permissions->search(function ($item, $key) use ($permission, $subKey) {
                    return $item->name == $permission && $item->guard_name == 'api';
                }) !== false) {
                $subKeys[$subKey] = 0;
            }
        }

        $statisticsCollection = collect([]);

        foreach ($keys as $key) {
            $statisticsCollection->put($key, $subKeys);
        }

        return $statisticsCollection;

    }

    public function incrementTotalRegistered($claim, $subKeys)
    {
        // allInstitution
        if ($subKeys!=null && array_key_exists('allInstitution', $subKeys)) {
            $subKeys['allInstitution']++;
        }

        // myInstitution
        try {
            if ($subKeys !=null
                && array_key_exists('myInstitution', $subKeys)
                && ($claim->createdBy->institution_id == $this->institution()->id)
            ) {
                $subKeys['myInstitution']++;
            }
        } catch (\Exception $exception) {
            if (
                $subKeys!=null
                && array_key_exists('myInstitution', $subKeys)
                && is_null($claim->createdBy)
                && ($claim->institution_targeted_id == $this->institution()->id)
            ) {
                $subKeys['myInstitution']++;
            }
        }

        // myUnit
        try {
            if (array_key_exists('myUnit', $subKeys) && $claim->createdBy->unit_id == $this->staff()->unit_id) {
                $subKeys['myUnit']++;
            }
        } catch (\Exception $exception) {
        }

        // myActivity
        try {
            if (array_key_exists('myActivity', $subKeys) && $claim->createdBy->id == $this->staff()->id) {
                $subKeys['myActivity']++;
            }
        } catch (\Exception $exception) {
        }

        return $subKeys;

    }

    public function incrementTotalCompleted($claim, $subKeys)
    {
        // allInstitution
        if (array_key_exists('allInstitution', $subKeys)) {
            $subKeys['allInstitution']++;
        }

        // myInstitution
        try {
            if (array_key_exists('myInstitution', $subKeys)
                && ($claim->status == 'full' && $claim->createdBy->institution_id == $this->institution()->id
                    || $claim->status == 'transferred_to_targeted_institution' && $claim->institution_targeted_id == $this->institution()->id)
            ) {
                $subKeys['myInstitution']++;
            }
        } catch (\Exception $exception) {

            if (
                array_key_exists('myInstitution', $subKeys)
                && $claim->status == 'full'
                && is_null($claim->createdBy)
                && $claim->institution_targeted_id == $this->institution()->id
            ) {
                $subKeys['myInstitution']++;
            }
        }

        // myUnit
        try {
            if (array_key_exists('myUnit', $subKeys)
                && $claim->completedBy->unit_id == $this->staff()->unit_id
            ) {
                $subKeys['myUnit']++;
            }
        } catch (\Exception $exception) {
        }

        // myActivity
        try {
            if (array_key_exists('myActivity', $subKeys)
                && $claim->completedBy->id == $this->staff()->id
            ) {
                $subKeys['myActivity']++;
            }
        } catch (\Exception $exception) {
        }

        return $subKeys;

    }

    public function incrementTotalTransferredToUnit($claim, $subKeys)
    {
        // allInstitution
        if (array_key_exists('allInstitution', $subKeys)) {
            $subKeys['allInstitution']++;
        }

        // myInstitution
        try {
            if (array_key_exists('myInstitution', $subKeys)
                && $claim->activeTreatment->responsibleUnit->institution_id == $this->institution()->id
            ) {
                $subKeys['myInstitution']++;
            }
        } catch (\Exception $exception) {
        }

        // myUnit
        try {
            if (array_key_exists('myUnit', $subKeys)
                && $claim->activeTreatment->responsibleUnit->id == $this->staff()->unit_id
            ) {
                $subKeys['myUnit']++;
            }
        } catch (\Exception $exception) {
        }

        // myActivity
        try {
            if (array_key_exists('myActivity', $subKeys)
                && $claim->activeTreatment->responsibleStaff->id == $this->staff()->id
            ) {
                $subKeys['myActivity']++;
            }
        } catch (\Exception $exception) {
        }

        return $subKeys;

    }

    public function incrementTotalMeasuredSatisfaction($claim, $subKeys)
    {
        // allInstitution
        if (array_key_exists('allInstitution', $subKeys)) {
            $subKeys['allInstitution']++;
        }

        // myInstitution
        try {
            if (array_key_exists('myInstitution', $subKeys)
                && $claim->activeTreatment->satisfactionMeasuredBy->institution_id == $this->institution()->id
            ) {
                $subKeys['myInstitution']++;
            }
        } catch (\Exception $exception) {
        }

        // myUnit
        try {
            if (array_key_exists('myUnit', $subKeys)
                && $claim->activeTreatment->satisfactionMeasuredBy->id == $this->staff()->unit_id
            ) {
                $subKeys['myUnit']++;
            }
        } catch (\Exception $exception) {
        }

        // myActivity
        try {
            if (array_key_exists('myActivity', $subKeys)
                && $claim->activeTreatment->satisfactionMeasuredBy->id == $this->staff()->id
            ) {
                $subKeys['myActivity']++;
            }
        } catch (\Exception $exception) {
        }

        return $subKeys;

    }

    public function incrementClaimerSatisfactionEvolution($claim, $subKeys)
    {
        // measured all Institution
        if (array_key_exists('allInstitution', $subKeys['measured'])) {
            $subKeys['measured']['allInstitution']++;
        }

        // measured my Institution
        try {
            if (array_key_exists('myInstitution', $subKeys['measured'])
                && $claim->activeTreatment->responsibleStaff->institution_id == $this->institution()->id
            ) {
                $subKeys['measured']['myInstitution']++;
            }
        } catch (\Exception $exception) {
        }

        // satisfied all Institution
        try {
            if (array_key_exists('allInstitution', $subKeys['satisfied'])
                && $claim->activeTreatment->is_claimer_satisfied == 1
            ) {
                $subKeys['satisfied']['allInstitution']++;
            }
        } catch (\Exception $exception) {
        }

        // satisfied my Institution
        try {
            if (array_key_exists('myInstitution', $subKeys['satisfied'])
                && $claim->activeTreatment->is_claimer_satisfied == 1
                && $claim->activeTreatment->responsibleStaff->institution_id == $this->institution()->id
            ) {
                $subKeys['satisfied']['myInstitution']++;
            }
        } catch (\Exception $exception) {
        }

        // unsatisfied all Institution
        try {
            if (array_key_exists('allInstitution', $subKeys['unsatisfied'])
                && $claim->activeTreatment->is_claimer_satisfied == 0
            ) {
                $subKeys['unsatisfied']['allInstitution']++;
            }
        } catch (\Exception $exception) {
        }

        // unsatisfied my Institution
        try {
            if (array_key_exists('myInstitution', $subKeys['unsatisfied'])
                && $claim->activeTreatment->is_claimer_satisfied == 0
                && $claim->activeTreatment->responsibleStaff->institution_id == $this->institution()->id
            ) {
                $subKeys['unsatisfied']['myInstitution']++;
            }
        } catch (\Exception $exception) {
        }

        return $subKeys;

    }

    public function incrementRegisteredEvolution($claim, $subKeys)
    {
        // registered all Institution
        if (array_key_exists('allInstitution', $subKeys['registered'])) {
            $subKeys['registered']['allInstitution']++;
        }

        // registered my Institution
        try {
            if (array_key_exists('myInstitution', $subKeys['registered'])
                && $claim->createdBy->institution_id == $this->institution()->id
            ) {
                $subKeys['registered']['myInstitution']++;
            }
        } catch (\Exception $exception) {
        }

        return $subKeys;

    }

    public function incrementProcessEvolution($claim, $subKeys, $status)
    {
        // transferred_to_unit all Institution
        if (array_key_exists('allInstitution', $subKeys[$status])) {
            $subKeys[$status]['allInstitution']++;
        }

        // transferred_to_unit my Institution
        try {
            if (array_key_exists('myInstitution', $subKeys[$status])
                && $claim->activeTreatment->responsibleUnit->institution_id == $this->institution()->id
            ) {
                $subKeys[$status]['myInstitution']++;
            }
        } catch (\Exception $exception) {
        }

        return $subKeys;
    }

    public function incrementTotalUnitsTargeted($claim, $subKeys)
    {
        // allInstitution
        try {
            if (array_key_exists('allInstitution', $subKeys)) {
                $subKeys['allInstitution']++;
            }
        } catch (\Exception $exception) {
        }


        // myInstitution
        try {
            if (array_key_exists('myInstitution', $subKeys) && $claim->unitTargeted->institution_id == $this->institution()->id) {
                $subKeys['myInstitution']++;
            }
        } catch (\Exception $exception) {
        }

        return $subKeys;

    }

    protected function formatMontWithYear($date)
    {
        list($shortMontName) = explode('.', $date->locale(App::getLocale())->shortMonthName);
        return $shortMontName . ' ' . $date->year;
    }


}