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
        $claims = Claim::query()
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
        ]);

        /*foreach ($months as $mouthName => $claimCategories){
            //$d[$mouthName] = $claimCategories;
           // array_push($d[$mouthName],$claimCategories);
            foreach ($claimCategories as $categoryName => $claimObjects){
                //dd($d[$mouthName][$categoryName]->toArray());
                //$s = $d[$mouthName][$categoryName]->toArray();
                //array_push($s,$claimObjects);
                foreach ($claimObjects as $claimObjectsName => $claims){
                    //$s[$mouthName][$categoryName][$claimObjectsName]['totalTreated'] = $this->totalTreated(collect($claims));
                    $obj = [
                        $claimObjectsName =>[
                            "totalTreated"=>$this->totalTreated(collect($claims)),
                            "totalReceived"=>count($claims),
                        ]
                    ];
                    $cat = [$categoryName => $obj];

                    $g = [
                      $mouthName =>$cat
                    ];
                    $data[$mouthName] = [];
                    array_push($data[$mouthName],$cat);
                    //return $g;
                    //dd($d[$mouthName][$categoryName][$claimObjectsName]);

                    $totalReceived = count($claims);
                    $totalTreated = $this->totalTreated($claims);

                    $data = [

                        "$d[$mouthName][$categoryName]"=>$totalTreated."fath"

                    ];
                    array_push($d[$mouthName][$claimObjectsName]);

                }
            }
        }*/
        //return $data;


        $claimCollection = collect([]);
        $data = [];

        $claims = $claims->map(function ($categories,$monthName) use ($claimCollection,$data){
            $categories->map(function ($objects, $categoryName) use ($claimCollection,$categories,$monthName){
                $objects->map(function ($claims, $objectName)  use ($claimCollection, $categoryName,$categories,$monthName){

                    $data = [
                        "month"=>$monthName,
                        'claimCategorie' => $categoryName,
                        'claimObject' => $objectName,
                        'totalReceived' => (string) $claims->count(),
                        'totalTreated' => (string) $this->totalTreated($claims),
                        'totalRemaining' => (string) ($claims->count()-$this->totalTreated($claims)),
                        'totalTreatedOutDelay' => (string) $this->totalTreatedOutDelay($this->claimTreated($claims)),
                    ];

                    return $claimCollection->push($data);
                });

            });
        });

        return $claimCollection->groupBy([
            function($item){
                return $item['month'];
            },
            function($item){
                return $item['claimCategorie'];
            },
            function($item){
                return $item['claimObject'];
            }
        ]);
    }
}