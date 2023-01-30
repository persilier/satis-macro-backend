<?php


namespace Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot;


use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Support\Facades\Notification;
use Satis2020\ServicePackage\Models\ActivePilot;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Notifications\RegisterAClaim;
use Satis2020\ServicePackage\Models\ConfigurationActivePilot;
use Satis2020\ServicePackage\Notifications\RegisterAClaimHighForcefulness;

trait ConfigurationPilotTrait
{
    use DataUserNature;

    protected function storeConfiguration($many_pilot)
    {
        return ConfigurationActivePilot::create([
            "many_active_pilot" => $many_pilot,
            "institution_id" => $this->institution()->id,
        ]);
    }

    protected function storeActivePilotAndLead($request)
    {
        $institution_id = $this->institution()->id;

        if ($request->many_pilot==true){
            ActivePilot::where( "institution_id",$institution_id)->delete();
            for ($i=0; $i<sizeof($request->pilots); $i++){
                ActivePilot::updateOrCreate(["staff_id"=>$request->pilots[$i]], [
                    "staff_id"=>$request->pilots[$i],
                    "institution_id"=>$institution_id,
                ]);
            }
        }

        return Institution::find($institution_id)->update(["active_pilot_id"=>$request->lead_pilot_id]);
    }

    protected function infoConfig($institution){
        $config = ConfigurationActivePilot::where("institution_id",$institution->id)
            ->orderBy("created_at","DESC")->get()->first();
        if ($config){
            $lead_pilot = $institution->leadActivePilot;
            $all_active_pilot = $institution->allActivePilot;
            return [
                "configuration"=>$config,
                "lead_pilot"=>$lead_pilot,
                "all_active_pilots"=> $config->many_active_pilot==true ? $all_active_pilot : null
            ];
        }else{
            $lead_pilot = $institution->leadActivePilot;
            return [
                "configuration"=>$config,
                "lead_pilot"=>$lead_pilot,
                "all_active_pilots"=> []
            ];
        }

    }

    protected function nowConfiguration(){
        $institution = $this->institution()->load("leadActivePilot","allActivePilot.staff.identite");
        return $this->infoConfig($institution);
    }


    protected function nowConfigurationWithUserId($user_id){
        $institution = $this->institutionWithUserId($user_id)->load("leadActivePilot","allActivePilot.staff");
        return $this->infoConfig($institution);
    }


    protected function notifyAllPilotAfterRegisterClaim($claim, $severityLevel, $user_id){
        $config = $this->nowConfigurationWithUserId($user_id);
        Log::info($config['configuration']);
        if ($config['configuration']->many_active_pilot){
            if ($severityLevel=="high"){
                for ($i=0;$i<sizeof($config->all_active_pilots);$i){
                    Notification::route('mail', [
                        $config->all_active_pilots[$i]["staff"]["identite"]["email"][0] =>  $config->all_active_pilots[$i]["staff"]["identite"]["firstname"],
                    ])->notify(new RegisterAClaimHighForcefulness($claim));
                }
            }else{
                for ($i=0;$i<sizeof($config->all_active_pilots);$i){
                    Notification::route('mail', [
                        $config->all_active_pilots[$i]["staff"]["identite"]["email"][0] =>  $config->all_active_pilots[$i]["staff"]["identite"]["firstname"],
                    ])->notify(new RegisterAClaim($claim));
                }
            }
        }
    }

}