<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Metadata;

trait AwaitingAssignment
{
    protected function getClaimsQuery($institutionId=null)
    {
        $institutionId = is_null($institutionId)? $this->institution()->id : $institutionId;

        $whereRawConditionVariables = [];
        $whereRawCondition = '( ';

        $whereRawCondition .= '(`staff`.`institution_id` = ? and `claims`.`status` = ?)';
        $whereRawConditionVariables[] = $institutionId;
        $whereRawConditionVariables[] = "full";

        $whereRawCondition .= ' or (`claims`.`institution_targeted_id` = ? and `claims`.`status` = ?)';
        $whereRawConditionVariables[] = $institutionId;
        $whereRawConditionVariables[] = "transferred_to_targeted_institution";

        $whereRawCondition .= ' or (`claims`.`status`= ? and `claims`.`created_by` is null and `claims`.`institution_targeted_id`= ?)';
        $whereRawConditionVariables[] = "full";
        $whereRawConditionVariables[] = $institutionId;

        $whereRawCondition .= ' )';

        return Claim::query()
            ->select('claims.*')
            ->leftJoin('staff', function ($join) {
                $join->on('claims.created_by', '=', 'staff.id');
            })
            ->whereRaw($whereRawCondition, $whereRawConditionVariables)
            ->whereNull('claims.deleted_at')
            ->whereNull('claims.revoked_at');

    }

    protected function getDuplicatesQuery($claim_query, $claim)
    {
        return $claim_query
            ->where('claims.claimer_id', "$claim->claimer_id")
            ->where('claims.claim_object_id', "$claim->claim_object_id")
            ->where('claims.id', '!=', "$claim->id");
    }

    protected function getDuplicates($claim)
    {
        return $this->getDuplicatesQuery($this->getClaimsQuery(), $claim)
            ->get()
            ->map(function ($item, $key) use ($claim) {
                $item = Claim::with($this->getRelations())->find($item->id);
                $item->duplicate_percent = intval($this->getPercent($claim, $item));
                try {
                    $item->is_mergeable = intval(Metadata::ofName('min-fusion-percent')->firstOrFail()->data) > $item->duplicate_percent
                        ? false
                        : true;
                } catch (\Exception $exception) {
                    $item->is_mergeable = true;
                }
                return $item;
            });
    }

    public function getRelations()
    {
        return Constants::getClaimRelations();
    }

    protected function getActiveTreatmentRelationsAwaitingAssignment()
    {
        return [
            'responsibleUnit',
            'assignedToStaffBy.identite',
            'responsibleStaff.identite',
            'satisfactionMeasuredBy.identite',
        ];
    }

    protected function getPercent($claim, $item)
    {
        $percent = 40;

        if ($claim->created_at->diffInDays($item->created_at) <= 2) {
            $percent += 10;
        }

        if (!is_null($claim->event_occured_at) && !is_null($item->event_occured_at)) {
            if ($claim->event_occured_at->diffInDays($item->event_occured_at) <= 2) {
                $percent += 10;
            }
        }

        if ($claim->description == $item->description) {
            $percent += 20;
        }

        if ($claim->amount_disputed == $item->amount_disputed && $claim->amount_currency_slug == $item->amount_currency_slug) {
            $percent += 20;
        }

        return $percent;

    }

    protected function showClaim($claim)
    {
        $claim->load($this->getRelations());

        $claim->duplicates = $this->getDuplicates($claim);

        if (!is_null($claim->activeTreatment)) {
            $claim->activeTreatment->load($this->getActiveTreatmentRelationsAwaitingAssignment());
        }

        return $claim;
    }
}