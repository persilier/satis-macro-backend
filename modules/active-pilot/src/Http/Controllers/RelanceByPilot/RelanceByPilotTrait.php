<?php


namespace Satis2020\ActivePilot\Http\Controllers\RelanceByPilot;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\RelanceOther;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Notifications\RegisterAClaimHighForcefulness;
use Satis2020\ServicePackage\Notifications\RelanceOtherNotif;

trait RelanceByPilotTrait
{
    protected function rules()
    {
        return [
            "message" => "required|string",
            "staff" => "required|string",
            "claim_id" => "required|string",
        ];
    }


    protected function storeRelance($request, $pilot)
    {

        $data = [
            "message" => $request->message,
            "staff_id" => $request->staff,
            "pilot_id" => $pilot->id,
        ];
        $staff = Staff::find($request->staff)->load("identite");
        $claim = Claim::findOrFail($request->claim_id)->load("activeTreatment");
        $treatment = $claim->activeTreatment;
        Treatment::findOrFail($treatment->id)->update(["transferred_to_unit_by" => $request->staff]);
        try {
            Notification::route('mail', [
                $staff["identite"]["email"][0] => $staff["identite"]["firstname"],
            ])->notify(new RelanceOtherNotif($request->message, $pilot["identite"]["firstname"] . " " . $pilot["identite"]["lastname"]));
        } catch (\Exception $e) {
            Log::error(["error" => $e->getMessage()]);
            return null;
        }
        return RelanceOther::create($data);
    }

}