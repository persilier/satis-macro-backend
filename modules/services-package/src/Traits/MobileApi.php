<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Database\Eloquent\Builder;
use Satis2020\ServicePackage\Models\Claim;

/**
 * Trait MobileApi
 * @package Satis2020\ServicePackage\Traits
 */
trait MobileApi
{

    /**
     * @param $date_start
     * @param $date_end
     * @return mixed
     */
    protected function totalClaimReceived($date_start, $date_end){

        return Claim::whereBetween('created_at', [$date_start, $date_end])->count();
    }


    /**
     * @param $date_start
     * @param $date_end
     * @return mixed
     */
    protected function totalClaimTreated($date_start, $date_end){

        return Claim::whereHas('activeTreatment', function($q) use ($date_start, $date_end){

            $q->whereBetween('validated_at', [$date_start, $date_end])->whereHas('responsibleUnit' , function ($p){

                $p->where('institution_id', $this->institution()->id);

            });

        })->where('request_channel_slug', 'mobile')->count();

    }


    /**
     * @param $date_start
     * @param $date_end
     * @return mixed
     */
    protected function totalClaimUntreated($date_start, $date_end){

        return Claim::whereBetween('created_at', [$date_start, $date_end])->whereHas('activeTreatment', function($q){

            $q->where('validated_at', NULL)->whereHas('responsibleUnit' , function ($p){

                $p->where('institution_id', $this->institution()->id);

            });

        })->orWhere(function ($o){

            $o->doesntHave('activeTreatment')->where('institution_targeted_id', $this->institution()->id);

        })->where('request_channel_slug', 'mobile')->count();

    }


    /**
     * @param $date_start
     * @param $date_end
     * @return float|int
     */
    protected function rateSatisfaction($date_start, $date_end){

        $nbreSatisfaction = $this->measureSatisfaction($date_start, $date_end);
        $nbreSatisfactionClient = $this->measureSatisfaction($date_start, $date_end, true);

        $rateSatisfaction = 0;

        if(($nbreSatisfaction != 0) && ($nbreSatisfactionClient != 0)){

            $rateSatisfaction = round(($nbreSatisfactionClient/$nbreSatisfaction), 2);
        }

        return $rateSatisfaction;
    }


    /**
     * @param $date_start
     * @param $date_end
     * @param bool $responseClient
     * @return mixed
     */
    protected function measureSatisfaction($date_start, $date_end, $responseClient = false){

        return Claim::whereHas('activeTreatment', function ($q) use ($date_start, $date_end, $responseClient){

            $q->whereBetween('satisfaction_measured_at', [$date_start, $date_end]);

            $q->whereHas('responsibleUnit' , function ($p){

                $p->where('institution_id', $this->institution()->id);

            });

            if($responseClient){

                $q->where('is_claimer_satisfied', 1);
            }

        })->where('request_channel_slug', 'mobile')->count();
    }


    /**
     * @param $date_start
     * @param $date_end
     * @return float
     */
    public function numberDaysMediumProcessing($date_start, $date_end){

        $nbreValidated = $this->claimTreated($date_start, $date_end)->count();

        $claimValidated = $this->claimTreated($date_start, $date_end)->get();

        $difValidatedTreated = 0;

        $numberDaysMedium = 0;

        foreach ($claimValidated as $value){

            $difValidatedTreated = $difValidatedTreated + ($value->created_at->copy()->diffInDays($value->activeTreatment->validated_at->copy()));
        }

        if(($nbreValidated != 0) && ($difValidatedTreated != 0)){

            $numberDaysMedium = round(($difValidatedTreated/$nbreValidated), 0);
        }

        return $numberDaysMedium;
    }


    /**
     * @param $date_start
     * @param $date_end
     * @return Builder
     */
    public function claimTreated($date_start, $date_end){

        return $claims = Claim::with('activeTreatment')->whereHas('activeTreatment', function ($q) use ($date_start, $date_end){

            $q->whereBetween('validated_at', [$date_start, $date_end])->whereHas('responsibleUnit' , function ($p){

                $p->where('institution_id', $this->institution()->id);

            });

        })->where('request_channel_slug', 'mobile');

    }


    /**
     * @return array
     */
    protected function rules(){

        $data = [

            'date_start' => 'required|date_format:Y-m-d',
            'date_end' => 'required|date_format:Y-m-d|after:date_start'
        ];

        return $data;
    }

}
