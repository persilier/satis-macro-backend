<?php

namespace Satis2020\BCIReports\Traits;

use Carbon\Carbon;
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
        $claims = $this->getReportData($institutionId, $year);

        $claimCollection = collect([]);

        $claims->map(function ($categories, $monthName) use ($claimCollection) {
            $categories->map(function ($objects, $categoryName) use ($claimCollection, $categories, $monthName) {
                $objects->map(function ($claims, $objectName) use ($claimCollection, $categoryName, $categories, $monthName) {
                    $data = [
                        "month" => $monthName,
                        'claimCategorie' => $categoryName,
                        'claimObject' => $objectName,
                        'totalReceived' => $claims->count(),
                        'totalTreated' => $this->totalTreated($claims),
                        'totalRemaining' => ($claims->count() - $this->totalTreated($claims)),
                        'totalTreatedOutDelay' => $this->totalTreatedOutDelay($this->claimTreated($claims)),
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


        $orderedData = [];
        $arr = [];
      /*  $months = [
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

        //set default values for empties months
        foreach ($months as $month) {
            $data = [
                "month" => $month,
                'totalReceived' => 0,
                'totalTreated' => 0,
                'totalRemaining' => 0,
                'totalTreatedOutDelay' => 0,
            ];
            $allData = [];

            if (!isset($response[$month])) {
                $response[$month] = [];
                foreach (ClaimCategory::with('claimObjects')->get() as $category) {
                    foreach ($category->claimObjects as $object) {
                        $data["claimObject"] = $object->name;
                        $data["claimCategorie"] = $category->name;

                        $fData = [$category->name => []];
                        $fData[$category->name] = [$object->name => []];
                        $fData[$category->name][$object->name] = $data;
                        array_push($allData, $fData);
                        $response[$month] = collect($allData);
                    }
                }
            } else {
                foreach (ClaimCategory::with('claimObjects')->get() as $category) {
                    foreach ($category->claimObjects as $object) {
                        if (!isset($response[$month][$category->name])) {
                            $fData = [$category->name => []];
                            $fData[$category->name] = [$object->name => []];
                            $fData[$category->name][$object->name] = $data;
                            array_push($allData, $fData);
                            $response[$month] = collect($allData);
                        } else {
                            if (!isset($response[$month][$category->name][$object->name])) {
                                $fData = [$category->name => []];
                                $fData[$category->name] = [$object->name => []];
                                $fData[$category->name][$object->name] = $data;
                                array_push($allData, $fData);
                                $response[$month] = collect($allData);
                            }
                        }
                    }
                }
            }
        }

        $response = $response->toArray();
        $response = $this->sortMonths();*/

      //  dd($response);

        foreach ($response as $month => $claimCategory) {
            array_push($orderedData, $claimCategory);
        }


        for ($i = 0; $i < count($orderedData); $i++) {
            $data = $orderedData[$i];
            $firstData = [];

            foreach ($data as $categoryName => $category) {
                foreach ($category as $objectName => $object) {
                    $newData = isset($object[0]) ? $object[0] : $object;
                    if (!array_key_exists(($i - 1), $orderedData)) {
                        $newData['initialStock'] = 0;
                        $newData['totalRemaining'] = ($newData['initialStock'] + $newData['totalReceived']) - $newData['totalTreated'];

                        $fData = [$categoryName => []];
                        $fData[$categoryName] = [$objectName => []];
                        $fData[$categoryName][$objectName] = $newData;

                        array_push($firstData, $fData);
                        $orderedData[0] = $firstData;
                    } else {
                        //find the record in the previous month
                        $previousMonth = $orderedData[$i - 1];


                        if (isset($previousMonth[$categoryName]) &&
                            isset($previousMonth[$categoryName][$objectName])
                        ) {
                            $value = $previousMonth[$categoryName][$objectName];
                        } else {
                            $value = null;
                        }

                        for ($k = 0; $k < count($previousMonth); $k++) {
                            //if (!isset($previousMonth[$k]))
                            //  dd($previousMonth,$k);
                            $elem = $previousMonth[$k];
                            if (isset($elem[$categoryName]) &&
                                isset($elem[$categoryName][$objectName])
                            ) {
                                $value = $elem[$categoryName][$objectName];
                                break;
                            } else {
                                $value = null;
                            }
                        }


                        if ($value != null) {
                            //dd($value);
                            //if ($newData['month']=="March" && $categoryName=="No Game No Life" && $objectName=="Garanties perdues")
                            //    dd($value,$previousMonth);
                            $newData['initialStock'] = $value['totalRemaining'];
                            $newData['totalRemaining'] = ($newData['initialStock'] + $newData['totalReceived']) - $newData['totalTreated'];
                        } else {
                            //dd($newData,$previousMonth);
                            //Log::info("$categoryName $objectName ".$newData['month']);
                            // dd($newData,$orderedData[$i-1]);
                            $newData['initialStock'] = 0;
                            if (!isset($newData['totalReceived']))
                                dd($newData, "miss");
                            $newData['totalRemaining'] = ($newData['initialStock'] + $newData['totalReceived']) - $newData['totalTreated'];
                        }


                        $fData = [$categoryName => []];
                        $fData[$categoryName] = [$objectName => []];
                        $fData[$categoryName][$objectName] = $newData;

                        array_push($firstData, $fData);
                        $orderedData[$i] = $firstData;
                    }

                    array_push($arr, $newData);
                }
            }
        }

        return $arr;

        /*   $previousMonth = null;
           $previousMonthAtt = null;
           $arr = [];
           $prevValue = null;
           foreach ($response as $month => $claimCategory) {
               foreach ($claimCategory as $categoryName => $claimObject) {
                   foreach ($claimObject as $objectName => $data) {
                       $newData = $data[0];
                       if ($previousMonth==null){
                           $newData['initialStock'] = 0;
                           $newData['totalRemaining'] = ($newData['initialStock'] + $newData['totalReceived'])-$newData['totalTreated'];
                           array_push($arr, $newData);
                       }else{
                           //find the record in the previous month
                           $previousMonth = collect($previousMonth);
                       }

                   }

               }
               $previousMonth = [$month=>$claimCategory];
           }*/
        return $response;


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
        foreach ($dataCollection[0] as $category) {
            unset($category['total']);
            foreach ($category as $object) {
                $totalYear['totalReceived'] += $object['totalReceived'];
                $totalYear['totalTreated'] += $object['totalTreated'];
                $totalYear['totalRemaining'] += $object['totalRemaining'];
                $totalYear['totalTreatedOutDelay'] += $object['totalTreatedOutDelay'];

            }
        }

        return ['reportData' => $dataCollection, 'totalReport' => $totalYear];
    }

    function sortMonths($array)
    {
        $orderedArray = [];
        foreach ($array as $month => $data) {
            switch ($month) {
                case "January":
                    $orderedArray[0] = $data;
                    break;
                case "February":
                    $orderedArray[1] = $data;
                    break;
                case "March":
                    $orderedArray[2] = $data;
                    break;
                case "April":
                    $orderedArray[3] = $data;
                    break;
                case "May":
                    $orderedArray[4] = $data;
                    break;
                case "June":
                    $orderedArray[5] = $data;
                    break;
                case "July":
                    $orderedArray[6] = $data;
                    break;
                case "August":
                    $orderedArray[7] = $data;
                    break;
                case "September":
                    $orderedArray[8] = $data;
                    break;
                case "October":
                    $orderedArray[9] = $data;
                    break;
                case "November":
                    $orderedArray[10] = $data;
                    break;
                case "December":
                    $orderedArray[11] = $data;
                    break;
            }
        }

        return $orderedArray;
    }
}