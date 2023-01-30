<?php
namespace Satis2020\ActivePilot\Http\Controllers\ReassignmentToPilot;


use Illuminate\Support\Facades\Notification;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ReassignmentToPilot;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Notifications\ReassignmentPilotNotify;
use Satis2020\ServicePackage\Notifications\RegisterAClaim;

trait ReassignmentToPilotTrait
{
    protected function rules(){
        return [
            "message" => "required|string",
            "pilot_id" => "required|exists:active_pilots,staff_id",
            "claim_id" => "required|exists:claims,id",
        ];
    }

    protected function storeReassignment($request, $lead, $user){
        $data = $request->all();
        $data["lead_pilot_id"] = $lead->id;
        $res = ReassignmentToPilot::create($data);
        $claim = Claim::find($request->claim_id);
        $pilot_to_reassign = Staff::find($request->pilot_id)->load("identite");
        $claim = Claim::findOrFail($request->claim_id)->load("activeTreatment");
        $treatment = $claim->activeTreatment;
        Treatment::findOrFail($treatment->id)->update(["transferred_to_unit_by" => $pilot_to_reassign->id]);
        Notification::route('mail', [
            $pilot_to_reassign["identite"]["email"][0] =>  $pilot_to_reassign["identite"]["firstname"],
        ])->notify(new ReassignmentPilotNotify($claim->reference,$user["identite"]["firstname"], $request->message));
        return $res;
    }

}