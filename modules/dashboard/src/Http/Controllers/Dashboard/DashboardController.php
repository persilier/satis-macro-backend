<?php

namespace Satis2020\Dashboard\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Traits\Dashboard;

class DashboardController extends ApiController
{

    use Dashboard;

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth:api');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $permissions = Auth::user()->getAllPermissions();

        // initialise statistics collection
        $statistics = $this->getDataCollection($this->getStatisticsKeys(), $permissions);

        // initialise total claims registered in last 30 days
        $totalClaimsRegisteredStatistics = collect(['total'=>0]);

        // initialise channelsUse collection
        $channelsUse = $this->getDataCollection(Channel::all()->pluck('name'),
            $permissions->filter(function ($value, $key) {
                return $value->name != 'show-dashboard-data-my-unit' && $value->name != 'show-dashboard-data-my-activity';
            })
        );

        // initialise claimObjectsUse collection
        $claimObjectsUse = $this->getDataCollection(ClaimObject::all()->pluck('name'),
            $permissions->filter(function ($value, $key) {
                return $value->name != 'show-dashboard-data-my-unit' && $value->name != 'show-dashboard-data-my-activity';
            })
        );

        // initialise claimerSatisfaction collection
        $claimerSatisfactionEvolution = $this->getDataCollectionMonthly($this->getDataCollection(['satisfied', 'unsatisfied', 'measured'],
            $permissions->filter(function ($value, $key) {
                return $value->name != 'show-dashboard-data-my-unit' && $value->name != 'show-dashboard-data-my-activity';
            })
        )->all());

        // initialise claimerProcessEvolution collection
        $claimerProcessEvolution = $this->getDataCollectionMonthly($this->getDataCollection(['registered', 'transferred_to_unit', 'unfounded', 'treated', 'measured'],
            $permissions->filter(function ($value, $key) {
                return $value->name != 'show-dashboard-data-my-unit' && $value->name != 'show-dashboard-data-my-activity';
            })
        )->all());

        Claim::withTrashed()
            ->with($this->getRelations())
            ->whereBetween('created_at', [
                Carbon::now()->startOfYear()->format('Y-m-d H:i:s'),
                Carbon::now()->endOfYear()->format('Y-m-d H:i:s')
            ])
            ->get()
            ->map(function ($claim, $key) use ($statistics, $channelsUse, $claimObjectsUse, $claimerSatisfactionEvolution, $claimerProcessEvolution, $totalClaimsRegisteredStatistics) {

                if ($claim->created_at->between(Carbon::now()->subDays(30), Carbon::now())) {

                    // totalRegistered
                    $totalClaimsRegisteredStatistics->put('total', ($totalClaimsRegisteredStatistics->get('total') + 1)) ;
                    $statistics->put('totalRegistered', $this->incrementTotalRegistered($claim, $statistics->get('totalRegistered')));

                    // totalIncomplete
                    if ($claim->status == 'incomplete') {
                        $statistics->put('totalIncomplete', $this->incrementTotalRegistered($claim, $statistics->get('totalIncomplete')));
                    }

                    // totalComplete
                    if ($claim->status == 'full' || $claim->status == 'transferred_to_targeted_institution') {
                        $statistics->put('totalComplete', $this->incrementTotalCompleted($claim, $statistics->get('totalComplete')));
                    }

                    if (!is_null($claim->activeTreatment)) {

                        $claim->activeTreatment->load($this->getActiveTreatmentRelations());

                        // totalTransferredToUnit
                        if ($claim->status == 'transferred_to_unit') {
                            $statistics->put('totalTransferredToUnit',
                                $this->incrementTotalTransferredToUnit($claim, $statistics->get('totalTransferredToUnit')));
                        }

                        // totalBeingProcess
                        if ($claim->status == 'assigned_to_staff') {
                            $statistics->put('totalBeingProcess',
                                $this->incrementTotalTransferredToUnit($claim, $statistics->get('totalBeingProcess')));
                        }

                        // totalTreated
                        if ($claim->status == 'treated') {
                            $statistics->put('totalTreated',
                                $this->incrementTotalTransferredToUnit($claim, $statistics->get('totalTreated')));
                        }

                        // totalUnfounded
                        if ($claim->status == 'archived' && !is_null($claim->activeTreatment->declared_unfounded_at)) {
                            $statistics->put('totalUnfounded',
                                $this->incrementTotalTransferredToUnit($claim, $statistics->get('totalUnfounded')));
                        }

                        // totalMeasuredSatisfaction
                        if ($claim->status == 'archived' && !is_null($claim->activeTreatment->satisfaction_measured_at)) {
                            $statistics->put('totalMeasuredSatisfaction',
                                $this->incrementTotalMeasuredSatisfaction($claim, $statistics->get('totalMeasuredSatisfaction')));
                        }
                    }

                    // channelsUse
                    $channelsUse->put($claim->requestChannel->name,
                        $this->incrementTotalRegistered($claim, $channelsUse->get($claim->requestChannel->name)));

                    // channelsUse
                    $claimObjectsUse->put($claim->claimObject->name,
                        $this->incrementTotalRegistered($claim, $claimObjectsUse->get($claim->claimObject->name)));
                }

                $claimerProcessEvolution->put($claim->created_at->month,
                    $this->incrementRegisteredEvolution($claim, $claimerProcessEvolution->get($claim->created_at->month)));

                if (!is_null($claim->activeTreatment)) {

                    $claim->activeTreatment->load($this->getActiveTreatmentRelations());

                    if (!is_null($claim->activeTreatment->transferred_to_unit_at)) {
                        $claimerProcessEvolution->put($claim->activeTreatment->transferred_to_unit_at->month,
                            $this->incrementProcessEvolution($claim
                                , $claimerProcessEvolution->get($claim->activeTreatment->transferred_to_unit_at->month)
                                , 'transferred_to_unit'
                            ));
                    }

                    if (!is_null($claim->activeTreatment->declared_unfounded_at)) {
                        $claimerProcessEvolution->put($claim->activeTreatment->declared_unfounded_at->month,
                            $this->incrementProcessEvolution($claim
                                , $claimerProcessEvolution->get($claim->activeTreatment->declared_unfounded_at->month)
                                , 'unfounded'
                            ));
                    }

                    if (!is_null($claim->activeTreatment->solved_at)) {
                        $claimerProcessEvolution->put($claim->activeTreatment->solved_at->month,
                            $this->incrementProcessEvolution($claim
                                , $claimerProcessEvolution->get($claim->activeTreatment->solved_at->month)
                                , 'treated'
                            ));
                    }

                    // claimerSatisfactionEvolution
                    if (!is_null($claim->activeTreatment->satisfaction_measured_at)) {

                        $claimerProcessEvolution->put($claim->activeTreatment->satisfaction_measured_at->month,
                            $this->incrementProcessEvolution($claim
                                , $claimerProcessEvolution->get($claim->activeTreatment->satisfaction_measured_at->month)
                                , 'measured'
                            ));

                        $claimerSatisfactionEvolution->put($claim->activeTreatment->satisfaction_measured_at->month,
                            $this->incrementClaimerSatisfactionEvolution($claim, $claimerSatisfactionEvolution->get($claim->activeTreatment->satisfaction_measured_at->month)));
                    }

                }

                return $claim;
            });

        return response()->json([
            'statistics' => $statistics,
            'channelsUse' => $channelsUse,
            'claimObjectsUse' => $claimObjectsUse,
            'claimerSatisfactionEvolution' => $claimerSatisfactionEvolution,
            'claimerProcessEvolution' => $claimerProcessEvolution,
            'totalClaimsRegisteredStatistics' => $totalClaimsRegisteredStatistics->get('total')
        ], 200);
    }

}
