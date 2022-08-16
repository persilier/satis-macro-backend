<?php

namespace Satis2020\BCIReports\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimCategory;

trait BCIReportsTrait
{

    protected function getReportData($institutionId, $year)
    {
        return Claim::query()
            ->with(['institutionTargeted', 'claimObject.claimCategory'])
            ->whereYear("created_at", $year)
            ->when($institutionId, function ($query) use ($institutionId) {
                $query->where('institution_targeted_id', $institutionId);
            })
            ->get()->groupBy([

                function ($item) {
                    return Carbon::parse($item->created_at)->format('F');
                },
                function ($item) {
                    return optional(optional($item->claimObject)->claimCategory)->name;
                },
                function ($item) {
                    return optional($item->claimObject)->name;
                }
            ]);
    }

    protected function getGlobalReportsByMonths($institutionId, $year)
    {
        $claims = $this->getReportData($institutionId, $year);

        $claimCollection = collect([]);

        $claims->map(function ($categories, $monthName) use ($claimCollection) {
            $categories->map(function ($objects, $categoryName) use ($claimCollection, $categories, $monthName) {
                $objects->map(function ($claims, $objectName) use ($claimCollection, $categoryName, $categories, $monthName) {

                    $data = [
                        "month" => $monthName,
                        'claimCategorie' => $categoryName,
                        'claimObject' => $objectName,
                        'totalReceived' => (string)$claims->count(),
                        'totalTreated' => (string)$this->totalTreated($claims),
                        'totalRemaining' => (string)($claims->count() - $this->totalTreated($claims)),
                        'totalTreatedOutDelay' => (string)$this->totalTreatedOutDelay($this->claimTreated($claims)),
                    ];

                    return $claimCollection->push($data);
                });

            });
        });

        $response = $claimCollection->groupBy([
            function ($item) {
                return $item['month'];
            },
            function ($item) {
                return $item['claimCategorie'];
            },
            function ($item) {
                return $item['claimObject'];
            }
        ]);

        $totalYear = ['totalReceived' => 0, "totalTreated" => 0, "totalRemaining" => 0, "totalTreatedOutDelay" => 0];
        foreach ($response as $categoryName => $claimCategories) {
            $total = ['totalReceived' => 0, "totalTreated" => 0, "totalRemaining" => 0, "totalTreatedOutDelay" => 0];
            foreach ($claimCategories as $claimObjects) {
                foreach ($claimObjects as $claimObject) {
                    $items = $claimObject[0];
                    $total['totalReceived'] += $items['totalReceived'];
                    $total['totalTreated'] += $items['totalTreated'];
                    $total['totalRemaining'] += $items['totalRemaining'];
                    $total['totalTreatedOutDelay'] += $items['totalTreatedOutDelay'];
                }
            }

            $response[$categoryName]['total'] = ["total" => [$total]];
            $totalYear['totalReceived'] += $total['totalReceived'];
            $totalYear['totalTreated'] += $total['totalTreated'];
            $totalYear['totalRemaining'] += $total['totalRemaining'];
            $totalYear['totalTreatedOutDelay'] += $total['totalTreatedOutDelay'];
        }

        return ['reportData' => $response, 'reportTotal' => $totalYear];
    }

    protected function getCondensedAnnualReports($institutionId, $year)
    {
        $categories = ClaimCategory::query()
            ->whereHas('claimObjects',function ($query) use($year){
                $query->whereHas('claims',function ($builder)use($year){
                    $builder->whereYear("created_at",$year);
                });
            })
            ->with("claimObjects.claims")
            ->get();

        $dataCollection = collect();


        $data = [];
        foreach ($categories as $category) {
            $data[$category->name] = [];
            $objects = $category->claimObjects;
            $totalCategory = ['totalReceived' => 0, "totalTreated" => 0, "totalRemaining" => 0, "totalTreatedOutDelay" => 0];

            foreach ($objects as $object) {
                $data[$category->name][$object->name] = [];
                $claims = $object->claims()->whereYear("created_at", $year)->get();
                $total['totalReceived'] = count($claims);
                $total['totalTreated'] = $this->totalTreated($claims);
                $total['totalRemaining'] = ($claims->count() - $this->totalTreated($claims));
                $total['totalTreatedOutDelay'] = $this->totalTreatedOutDelay($this->claimTreated($claims));

                $totalCategory['totalReceived'] += $total['totalReceived'];
                $totalCategory['totalTreated'] += $total['totalTreated'];
                $totalCategory['totalRemaining'] += $total['totalRemaining'];
                $totalCategory['totalTreatedOutDelay'] += $total['totalTreatedOutDelay'];

                $data[$category->name][$object->name] = $total;
            }
            $data[$category->name]["total"] = $totalCategory;

        }
        $dataCollection->push($data);

        $totalYear = ['totalReceived' => 0, "totalTreated" => 0, "totalRemaining" => 0, "totalTreatedOutDelay" => 0];
        foreach ($dataCollection[0] as $category){
            unset($category['total']);
            foreach ($category as $object){
                $totalYear['totalReceived'] += $object['totalReceived'];
                $totalYear['totalTreated'] += $object['totalTreated'];
                $totalYear['totalRemaining'] += $object['totalRemaining'];
                $totalYear['totalTreatedOutDelay'] += $object['totalTreatedOutDelay'];

            }
        }

        return ['reportData'=>$dataCollection,'totalReport'=>$totalYear];
    }
}