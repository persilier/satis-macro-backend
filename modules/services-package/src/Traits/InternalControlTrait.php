<?php


namespace Satis2020\ServicePackage\Traits;


use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\ConfigInternalControl;
use Satis2020\ServicePackage\Models\ConfigurationActivePilot;

trait InternalControlTrait
{
    use DataUserNature, FilterClaims, ClaimSatisfactionMeasured;

    protected function storeConfiguration($state)
    {
        return ConfigInternalControl::create([
            "state" => $state,
            "user_id" => $this->user()->id,
            "institution_id" => $this->institution()->id,
        ]);
    }

    protected function configInternalControl($institution){
        return ConfigInternalControl::where("institution_id",$institution->id)
            ->orderBy("created_at","DESC")->get()->first();
    }

    protected function infoConfigInternalControl(){
        return $this->configInternalControl($this->institution());
    }


    protected function claim($request){
        $response = [];
        $claim_object_ids = $request->claim_object_ids;
        if (sizeof($claim_object_ids)==0){
            $claim_object_ids = ClaimObject::where("internal_control",true)->get()->pluck("id")->toArray();
        }
        $paginate = $request->size;
        $claimReceived = $this->getClaimsReceivedWithClaimObjectForInternalControl($request,$claim_object_ids)->count();
        $claimTreated = $this->getClaimsTreatedWithClaimObjectForInternalControl($request,$claim_object_ids)->count();
        $claimNotTreated = $this->getClaimsNotTreatedWithClaimObjectForInternalControl($request,$claim_object_ids)->count();
        $claimAverageTimeTreatment = $this->getAverageTimeClaimsTreatedWithClaimObjectForInternalControl($request,$claim_object_ids);
        $claimSatisfactionMeasured = $this->getSatisfactionClaimsTreatedWithClaimObjectForInternalControl($request,$claim_object_ids,true)->count();
        $claimNotSatisfactionMeasured = $this->getSatisfactionClaimsTreatedWithClaimObjectForInternalControl($request,$claim_object_ids,false)->count();
        $claimReceivedList = $this->getClaimsReceivedListCustomWithClaimObjectForInternalControl($request,$claim_object_ids)->paginate($paginate);
        $response["claimReceived"] = $claimReceived;
        $response["claimTreated"] = $claimTreated;
        $response["claimNotTreated"] = $claimNotTreated;
        $response["claimAverageTimeTreatment"] = $claimAverageTimeTreatment;
        $response["claimSatisfactionMeasured"] = $claimSatisfactionMeasured;
        $response["claimNotSatisfactionMeasured"] = $claimNotSatisfactionMeasured;
        $response["claimReceivedList"] = $claimReceivedList;
        return $response;
    }

}