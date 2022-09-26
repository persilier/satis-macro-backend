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
            ->orderBy('created_at', 'ASC')
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

        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $monthsNames = [
            "January",
            "February",
            "March",
            "April",
            "May",
            "June",
            "July",
            "August",
            "September",
            "October",
            "November",
            "December"
        ];
        $monthlyClaims = [];
        $monthlyClaimsActualized = [];

        for ($i = 0; $i < count($months); $i++) {
            foreach (ClaimCategory::with('claimObjects.claims')->get() as $category) {
                foreach ($category->claimObjects as $object) {
                    $claims = $object->claims()
                        ->whereYear("created_at", $year)
                        ->whereMonth("created_at", $months[$i])
                        ->get();

                    if (empty($claims)) {
                        $data = [
                            "month" => $monthsNames[$i],
                            'claimCategorie' => $category->name,
                            'claimObject' => $object->name,
                            'totalReceived' => 0,
                            'totalTreated' => 0,
                            'totalRemaining' => 0,
                            'totalTreatedOutDelay' => 0,
                            'totalRemainingOutDelay' => 0,
                        ];
                    } else {
                        $data = [
                            "month" => $monthsNames[$i],
                            'claimCategorie' => $category->name,
                            'claimObject' => $object->name,
                            'totalReceived' => $claims->count(),
                            'totalTreated' => $this->totalTreated($claims),
                            'totalRemaining' => ($claims->count() - $this->totalTreated($claims)),
                            'totalTreatedOutDelay' => $this->totalTreatedOutDelay($this->claimTreated($claims)),
                            'totalRemainingOutDelay' => $this->totalOutDelay($this->claimsNotTreated($claims)),
                        ];
                    }
                    array_push($monthlyClaims, $data);
                }
            }

        }

        for ($k = 0; $k < count($months); $k++) {
            foreach (ClaimCategory::with('claimObjects.claims')->get() as $category) {
                foreach ($category->claimObjects as $object) {

                    if ($k > 0) {
                        $previousMonth = collect($monthlyClaimsActualized)->filter(function ($item, $key) use ($months, $k, $monthsNames, $category, $object, $monthlyClaimsActualized) {
                            return $item['month'] == $monthsNames[$k - 1] && $item['claimCategorie'] == $category->name && $item['claimObject'] == $object->name;
                        })->first();
                    } else {
                        $decemberLastYearClaims = $object->claims()
                            ->whereYear("created_at", $year - 1)
                            ->whereMonth("created_at", 12)
                            ->get();

                        if (empty($decemberLastYearClaims)) {
                            $decemberData = [
                                "month" => $monthsNames[11],
                                'claimCategorie' => $category->name,
                                'claimObject' => $object->name,
                                'totalReceived' => 0,
                                'totalTreated' => 0,
                                'totalRemaining' => 0,
                                'totalTreatedOutDelay' => 0,
                            ];
                        } else {
                            $decemberData = [
                                "month" => $monthsNames[11],
                                'claimCategorie' => $category->name,
                                'claimObject' => $object->name,
                                'totalReceived' => $decemberLastYearClaims->count(),
                                'totalTreated' => $this->totalTreated($decemberLastYearClaims),
                                'totalRemaining' => ($decemberLastYearClaims->count() - $this->totalTreated($decemberLastYearClaims)),
                                'totalTreatedOutDelay' => $this->totalTreatedOutDelay($this->claimTreated($decemberLastYearClaims)),
                                'totalRemainingOutDelay' => $this->totalOutDelay($this->claimsNotTreated($decemberLastYearClaims)),                            ];
                        }
                        $previousMonth = $decemberData;
                    }

                    $currentMonth = collect($monthlyClaims)->filter(function ($item, $key) use ($months, $k, $monthsNames, $category, $object, $monthlyClaimsActualized) {
                        return $item['month'] == $monthsNames[$k] && $item['claimCategorie'] == $category->name && $item['claimObject'] == $object->name;
                    })->first();

                    $currentMonth['initialStock'] = $previousMonth['totalRemaining'];
                    $currentMonth['totalRemaining'] = ($currentMonth['initialStock'] + $currentMonth['totalReceived']) - $currentMonth['totalTreated'];

                    array_push($monthlyClaimsActualized, $currentMonth);
                }
            }

        }

        $groupedData = collect($monthlyClaimsActualized)->groupBy([
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

        $totalYear = [
            'totalReceived' => 0,
            "totalTreated" => 0,
            "totalRemaining" => 0,
            "totalTreatedOutDelay" => 0,
            "totalRemainingOutDelay" => 0,
            "totalInitialStock" => 0
        ];

        foreach ($groupedData as $categoryName => $claimCategories) {
            $total = [
                "totalReceived" => 0,
                "totalTreated" => 0,
                "totalRemaining" => 0,
                "totalTreatedOutDelay" => 0,
                "totalRemainingOutDelay" => 0,
                "totalInitialStock" => 0
            ];
            foreach ($claimCategories as $claimObjects) {
                foreach ($claimObjects as $claimObject) {
                    $items = $claimObject[0];
                    //dd($items);
                    $total['totalReceived'] += $items['totalReceived'];
                    $total['totalTreated'] += $items['totalTreated'];
                    $total['totalRemaining'] += $items['totalRemaining'];
                    $total['totalTreatedOutDelay'] += $items['totalTreatedOutDelay'];
                    $total['totalRemainingOutDelay'] += $items['totalRemainingOutDelay'];
                    $total['totalInitialStock'] += $items['initialStock'];
                }
            }

            $groupedData[$categoryName]['total'] = ["total" => [$total]];
            //$totalYear['totalInitialStock'] += $total['totalInitialStock'];
            $totalYear['totalReceived'] += $total['totalReceived'];
            $totalYear['totalTreated'] += $total['totalTreated'];
            $totalYear['totalRemaining'] += $total['totalReceived'] - $total['totalTreated'];
            $totalYear['totalTreatedOutDelay'] += $total['totalTreatedOutDelay'];
            $totalYear['totalRemainingOutDelay'] += $total['totalRemainingOutDelay'];
        }

        return ['reportData' => $groupedData, 'reportTotal' => $totalYear];
    }

    protected function getCondensedAnnualReports($institutionId, $year)
    {
        $categories = ClaimCategory::query()
            ->whereHas('claimObjects', function ($query) use ($year) {
                $query->whereHas('claims', function ($builder) use ($year) {
                    $builder->whereYear("created_at", $year);
                });
            })
            ->with("claimObjects.claims")
            ->get();

        $dataCollection = collect();

        $data = [];
        foreach ($categories as $category) {
            $data[$category->name] = [];
            $objects = $category->claimObjects;
            $totalCategory = [
                'totalReceived' => 0,
                "totalTreated" => 0,
                "totalRemaining" => 0,
                "totalTreatedOutDelay" => 0,
                "totalRemainingOutDelay" => 0
            ];

            foreach ($objects as $object) {
                $data[$category->name][$object->name] = [];
                $claims = $object->claims()->whereYear("created_at", $year)->get();
                $total['totalReceived'] = count($claims);
                $total['totalTreated'] = $this->totalTreated($claims);
                $total['totalRemaining'] = ($claims->count() - $this->totalTreated($claims));
                $total['totalTreatedOutDelay'] = $this->totalTreatedOutDelay($this->claimTreated($claims));
                $total['totalRemainingOutDelay'] = $this->totalOutDelay($this->claimsNotTreated($claims));

                $totalCategory['totalReceived'] += $total['totalReceived'];
                $totalCategory['totalTreated'] += $total['totalTreated'];
                $totalCategory['totalRemaining'] += $total['totalRemaining'];
                $totalCategory['totalTreatedOutDelay'] += $total['totalTreatedOutDelay'];
                $totalCategory['totalRemainingOutDelay'] += $total['totalRemainingOutDelay'];

                $data[$category->name][$object->name] = $total;
            }
            $data[$category->name]["total"] = $totalCategory;

        }
        $dataCollection->push($data);

        $totalYear = [
            'totalReceived' => 0,
            "totalTreated" => 0,
            "totalRemaining" => 0,
            "totalTreatedOutDelay" => 0,
            "totalRemainingOutDelay" => 0
        ];
        foreach ($dataCollection[0] as $category) {
            unset($category['total']);
            foreach ($category as $object) {
                $totalYear['totalReceived'] += $object['totalReceived'];
                $totalYear['totalTreated'] += $object['totalTreated'];
                $totalYear['totalRemaining'] += $object['totalRemaining'];
                $totalYear['totalTreatedOutDelay'] += $object['totalTreatedOutDelay'];
                $totalYear['totalRemainingOutDelay'] += $object['totalRemainingOutDelay'];

            }
        }

        return ['reportData' => $dataCollection, 'totalReport' => $totalYear];
    }

}