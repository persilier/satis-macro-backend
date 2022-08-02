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


        $claimCollection = collect([]);

        $claims = $claims->map(function ($categories,$monthName) use ($claimCollection){
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

        $response =  $claimCollection->groupBy([
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

        $totalYear = ['totalReceived'=>0,"totalTreated"=>0,"totalRemaining"=>0,"totalTreatedOutDelay"=>0];
        foreach ($response as $categoryName => $claimCategories){
            $total = ['totalReceived'=>0,"totalTreated"=>0,"totalRemaining"=>0,"totalTreatedOutDelay"=>0];
            foreach ($claimCategories as $claimObjects){
                foreach ($claimObjects as $claimObject){
                    $items = $claimObject[0];
                    $total['totalReceived'] += $items['totalReceived'];
                    $total['totalTreated'] += $items['totalTreated'];
                    $total['totalRemaining'] += $items['totalRemaining'];
                    $total['totalTreatedOutDelay'] += $items['totalTreatedOutDelay'];
                }
            }

            $response[$categoryName]['total'] = $total;
            $totalYear['totalReceived'] += $total['totalReceived'];
            $totalYear['totalTreated'] += $total['totalTreated'];
            $totalYear['totalRemaining'] += $total['totalRemaining'];
            $totalYear['totalTreatedOutDelay'] += $total['totalTreatedOutDelay'];
        }

        return $response;
        //return ['reportData'=>$response,'reportTotal' => $totalYear];
    }
}