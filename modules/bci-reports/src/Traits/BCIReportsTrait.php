<?php
namespace Satis2020\BCIReports\Traits;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use phpDocumentor\Reflection\DocBlock\Tags\Reference\Url;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimCategory;

trait BCIReportsTrait
{

    protected function getClaimsByCategories($institutionId,$year=null)
    {
        $months = Claim::query()
            ->with(['institutionTargeted','claimObject.claimCategory'])
            ->whereYear("created_at",now())
            ->get()->groupBy([

            function($item){
                return Carbon::parse($item->created_at)->format('F');
            },
            function($item){
                return optional(optional($item->claimObject)->claimCategory)->name;
            },
            function($item){
                return optional($item->claimObject)->name;
            }
        ])->toArray();

        $d = [];
        foreach ($months as $mouthName => $claimCategories){
            $d[$mouthName] = $claimCategories;
           // array_push($d[$mouthName],$claimCategories);
            dd($d);
            foreach ($claimCategories as $categoryName => $claimObjects){
                array_push($d[$mouthName][$categoryName],$claimObjects);
                dd($d);
                foreach ($claimObjects as $claimObjectsName => $claims){
                    $totalReceived = count($claims);
                    $totalTreated = $this->totalTreated($claims);

                    $data = [

                        "totalTreated"=>$totalTreated

                    ];
                    array_push($d[$mouthName][$claimObjectsName]);

                }
            }
        }

        $data = [];
        $claimCollection = collect([]);


        $claims = $claims->map(function ($categories,$month) use ($claimCollection,$data){

            $categories->map(function ($objects, $keyCategory) use ($claimCollection,$categories,$data){



                $objects->map(function ($claims, $keyObject)  use ($claimCollection, $keyCategory,$categories,$data){



                    /*$data = [
                        'claimCategorie' => $keyCategory,
                        'claimObject' => $keyObject,
                        'totalClaim' => (string) $claims->count(),
                        'totalTreated' => (string) $this->totalTreated($claims),
                        'totalUnfounded' => (string) $this->totalUnfounded($claims),
                        'totalNoValidated' => (string) $this->totalNoValidated($claims),
                        'delayMediumQualification' => (string) $this->delayMediumQualification($claims),
                        'delayPlanned' => optional((string) $claims->first()->claimObject)->time_limit,
                        'delayMediumTreatmentWithWeekend' => (string)  $this->delayMediumTreatmentWithWeekend($claims),
                        'delayMediumTreatmentWithoutWeekend' => (string)  $this->delayMediumTreatmentWithoutWeekend($claims),
                        'percentageTreatedInDelay' => (string)  $this->percentageInTime($claims),
                        'percentageTreatedOutDelay' => (string)  $this->percentageOutTime($claims),
                        'percentageNoTreated' => (string) $this->percentageNotTreated($claims)
                    ];*/


                });

            });
            return $claimCollection->push($data);
        });

        return $claimCollection;
    }
}