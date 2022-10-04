<?php

namespace Satis2020\BCIReports\Traits;

use Satis2020\ServicePackage\Models\ClaimCategory;

trait BCIReportsTrait
{

    //get Global Reports By Months
    protected function getGlobalReportsByMonths($institutionId, $year)
    {

        $currentYear = date("Y");

        $months = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];

        if ($year===$currentYear){
            $monthNumber = date('m');
            $months = range(1,$monthNumber);
        }

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


        // get total claim by month and grouped by category object
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
                           // ->whereMonth("created_at", 12)
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
                                'totalRemainingOutDelay' => $this->totalOutDelay($this->claimsNotTreated($decemberLastYearClaims)),];
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
                    $total['totalReceived'] += $items['totalReceived'];
                    $total['totalTreated'] += $items['totalTreated'];
                    $total['totalRemaining'] += $items['totalRemaining'];
                    $total['totalTreatedOutDelay'] += $items['totalTreatedOutDelay'];
                    $total['totalRemainingOutDelay'] += $items['totalRemainingOutDelay'];
                    $total['totalInitialStock'] += $items['initialStock'];
                }
            }

            $groupedData[$categoryName]['total'] = ["total" => [$total]];
            $totalYear['totalReceived'] += $total['totalReceived'];
            $totalYear['totalTreated'] += $total['totalTreated'];
            $totalYear['totalRemaining'] += $total['totalReceived'] - $total['totalTreated'];
            $totalYear['totalTreatedOutDelay'] += $total['totalTreatedOutDelay'];
            $totalYear['totalRemainingOutDelay'] += $total['totalRemainingOutDelay'];
        }

        return ['reportData' => $groupedData, 'reportTotal' => $totalYear];
    }


    //get Condensed Annual Reports
    protected function getCondensedAnnualReports($institutionId, $year)
    {
        $yearlyClaims = [];

        //get collection of previous year report
        $previousYearData = $this->getPreviousYearGlobalReport($institutionId, $year);

        //group yearly total claims by category and object
        foreach (ClaimCategory::with('claimObjects.claims')->get() as $category) {
            foreach ($category->claimObjects as $object) {
                $claims = $object->claims()
                    ->whereYear("created_at", $year)
                    ->get();

                if (empty($claims)) {
                    $data = [
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
                        'claimCategorie' => $category->name,
                        'claimObject' => $object->name,
                        'totalReceived' => $claims->count(),
                        'totalTreated' => $this->totalTreated($claims),
                        'totalRemaining' => ($claims->count() - $this->totalTreated($claims)),
                        'totalTreatedOutDelay' => $this->totalTreatedOutDelay($this->claimTreated($claims)),
                        'totalRemainingOutDelay' => $this->totalOutDelay($this->claimsNotTreated($claims)),
                    ];
                }
                array_push($yearlyClaims, $data);
            }
        }

        $groupedData = collect($yearlyClaims)->groupBy([
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
            "totalStockInitial" => 0
        ];

        foreach ($groupedData as $categoryName => $category) {
            $totalCategory = [
                'totalReceived' => 0,
                "totalTreated" => 0,
                "totalRemaining" => 0,
                "totalTreatedOutDelay" => 0,
                "totalRemainingOutDelay" => 0,
                "totalStockInitial" => 0,
            ];

            //add initial stock(previous year claim) to the yearly collection
            $totalInitial = 0;
            foreach ($category as $objectName => $object) {
                $data = $object[0];
                if ($object[0]) {
                    $data['initialStock'] = $previousYearData[$categoryName][$objectName][0]['totalRemaining'];
                    $groupedData[$categoryName][$objectName] = $data;
                    $totalInitial += $data['initialStock'];

                    $totalCategory['totalReceived'] += $data['totalReceived'];
                    $totalCategory['totalTreated'] += $data['totalTreated'];
                    $totalCategory['totalRemaining'] += $data['totalRemaining'];
                    $totalCategory['totalTreatedOutDelay'] += $data['totalTreatedOutDelay'];
                    $totalCategory['totalRemainingOutDelay'] += $data['totalRemainingOutDelay'];
                    $totalCategory['totalStockInitial'] = $totalInitial;

                    $groupedData[$categoryName]['total'] = $totalCategory;
                }
            }

            //condense total claim by category
            $totalYear['totalReceived'] += $totalCategory['totalReceived'];
            $totalYear['totalTreated'] += $totalCategory['totalTreated'];
            $totalYear['totalRemaining'] += $totalCategory['totalRemaining'];
            $totalYear['totalTreatedOutDelay'] += $totalCategory['totalTreatedOutDelay'];
            $totalYear['totalRemainingOutDelay'] += $totalCategory['totalRemainingOutDelay'];
            $totalYear['totalStockInitial'] += $totalCategory['totalStockInitial'];
        }

        return ['reportData' => $groupedData, 'totalReport' => $totalYear];
    }

    //get Previous Year Global Report (total of claim by category and object)
    protected function getPreviousYearGlobalReport($institutionId, $year)
    {

        $allData = [];
        $previousYear = $year - 1;

        foreach (ClaimCategory::with('claimObjects.claims')->get() as $category) {
            foreach ($category->claimObjects as $object) {
                $claims = $object->claims()
                    ->whereYear("created_at", $previousYear)
                    ->get();

                if (empty($claims)) {
                    $data = [
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
                        'claimCategorie' => $category->name,
                        'claimObject' => $object->name,
                        'totalReceived' => $claims->count(),
                        'totalTreated' => $this->totalTreated($claims),
                        'totalRemaining' => ($claims->count() - $this->totalTreated($claims)),
                        'totalTreatedOutDelay' => $this->totalTreatedOutDelay($this->claimTreated($claims)),
                        'totalRemainingOutDelay' => $this->totalOutDelay($this->claimsNotTreated($claims)),
                    ];
                }
                array_push($allData, $data);
            }
        }

        return collect($allData)->groupBy([
            function ($item) {
                return $item['claimCategorie'];
            },
            function ($item) {
                return $item['claimObject'];
            }
        ]);
    }

}