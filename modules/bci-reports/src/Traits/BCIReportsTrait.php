<?php
namespace Satis2020\BCIReports\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Models\Claim;

trait BCIReportsTrait
{

    protected function getReportData($institutionId,$year)
    {
        return Claim::query()
            ->with(['institutionTargeted','claimObject.claimCategory'])
            ->whereYear("created_at",$year)
            ->when($institutionId,function ($query) use($institutionId){
                $query->where('institution_targeted_id',$institutionId);
            })
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
    }

    protected function getGlobalReportsByMonths($institutionId,$year)
    {
        $claims = $this->getReportData($institutionId,$year);

        $claimCollection = collect([]);

        $claims->map(function ($categories,$monthName) use ($claimCollection){
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

                $response[$categoryName]['total'] = ["total"=>[$total]];
                $totalYear['totalReceived'] += $total['totalReceived'];
                $totalYear['totalTreated'] += $total['totalTreated'];
                $totalYear['totalRemaining'] += $total['totalRemaining'];
                $totalYear['totalTreatedOutDelay'] += $total['totalTreatedOutDelay'];
            }

        return ['reportData'=>$response,'reportTotal' => $totalYear];
    }

    protected function getCondensedAnnualReports($institutionId,$year)
    {
        $claims = $this->getReportData($institutionId,$year);

        $claimCollection = collect([]);

        $claims->map(function ($categories,$monthName) use ($claimCollection){
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

        $claims =  $claimCollection->groupBy([
            function($item){
                return $item['month'];
            },
            function($item){
                return $item['claimCategorie'];
            },
            function($item){
                return $item['claimObject'];
            },

        ]);

        $tab = [];
        //$totalYear = ['totalReceived'=>0,"totalTreated"=>0,"totalRemaining"=>0,"totalTreatedOutDelay"=>0];

        //return $claims;
        foreach ($claims as  $key_month=>$value_month){
            $totalYear[$key_month] = ['totalReceived'=>0,"totalTreated"=>0,"totalRemaining"=>0,"totalTreatedOutDelay"=>0];
            foreach ($value_month as  $key_cat=>$value_cat){
                $tab[$key_cat] = [];
                foreach ($value_cat as  $key_obj=>$value_obj){
                    foreach ($value_obj as $item){
                        Log::info("-------$key_month-------------");
                        Log::debug($item);
                        Log::info("----------$key_month----------");

                        $totalYear['totalReceived'] += $item['totalReceived'];
                        $totalYear['totalTreated'] += $item['totalTreated'];
                        $totalYear['totalRemaining'] += $item['totalRemaining'];
                        $totalYear['totalTreatedOutDelay'] += $item['totalTreatedOutDelay'];

                    }
                    $tab[$key_cat][$key_obj] = $totalYear;
                }
            }
            Log::info("-----------------------total-$key_month------------------");
            Log::debug($totalYear);
            Log::info("-----------------------total-$key_month-----------------");
        }
        return $tab;

      /*  $annualData = collect();
        $totalYear = ['totalReceived'=>0,"totalTreated"=>0,"totalRemaining"=>0,"totalTreatedOutDelay"=>0];
        $data = [];
        $response->map(function ($item) use ($annualData){
            //$annualData->push([])
        });*/

        return $response;
        /*$claims->map(function ($categories,$monthName) use ($claimCollection){
            $categories->map(function ($objects, $categoryName) use ($claimCollection,$categories,$monthName){
                $objects->map(function ($claims, $objectName)  use ($claimCollection, $categoryName,$categories,$monthName){

                    $data = [
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

        return   $claimCollection->groupBy([
            function($item){
                return $item['claimCategorie'];
            },
            function($item){
                return $item['claimObject'];
            }
        ]);*/
    }
}