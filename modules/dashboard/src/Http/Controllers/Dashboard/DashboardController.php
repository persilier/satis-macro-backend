<?php

namespace Satis2020\Dashboard\Http\Controllers\Dashboard;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Requests\Monitoring\DashboardRequest;
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
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Illuminate\Validation\ValidationException
     */

    public function index(DashboardRequest $request)
    {

        $permissions = Auth::user()->getAllPermissions();

        $institutions = Institution::query()->whereHas('institutionType', function ($query) {
            $query->where('name','<>','holding');
        })->get();

        // initialise statistics collection
        $statistics = $this->getDataCollection($this->getStatisticsKeys(), $permissions);

        // initialise total claims registered in last 30 days
        $totalClaimsRegisteredStatistics = collect(['total' => 0]);

        // initialise channelsUse collection
        $channelsUse = $this->getDataCollection(Channel::all()->pluck('name'),
            $permissions->filter(function ($value, $key) {
                return $value->name != 'show-dashboard-data-my-unit' && $value->name != 'show-dashboard-data-my-activity';
            })
        );

        // initialise pointOfServicesTargeted collection
        $pointOfServicesTargeted = $this->getDataCollection($this->institution()
            ->units()
            ->whereHas('unitType', function ($q) {
                $q->where('can_be_target', true);
            })->get()
            ->pluck('name'),
            $permissions->filter(function ($value, $key) {
                return $value->name != 'show-dashboard-data-all-institution' && $value->name != 'show-dashboard-data-my-unit' && $value->name != 'show-dashboard-data-my-activity';
            })
        );

        // initialise institutionsTargeted collection
        $institutionsTargeted = $this->getDataCollection($institutions->pluck('name'),
            $permissions->filter(function ($value, $key) {
                return $value->name != 'show-dashboard-data-my-institution' && $value->name != 'show-dashboard-data-my-unit' && $value->name != 'show-dashboard-data-my-activity';
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
                Carbon::now()->subMonths(11)->format('Y-m-d H:i:s'),
                Carbon::now()->format('Y-m-d H:i:s')
            ])
            ->where(function($q) use ($request){
                $request->has('institution_targeted_id') ? $q->where('institution_targeted_id', $request->institution_targeted_id) : $q->where('institution_targeted_id', '!=', NULL);
            })
            ->get()
            ->map(function ($claim, $key) use ($request, $statistics, $channelsUse, $claimObjectsUse, $claimerSatisfactionEvolution, $claimerProcessEvolution, $totalClaimsRegisteredStatistics, $pointOfServicesTargeted, $institutionsTargeted) {

               switch ($request->type){
                   case "day":
                       $periode = $claim->created_at->between(Carbon::parse($request->day)->startOfDay(), Carbon::parse($request->day)->endOfDay());
                       break;
                   case "period":
                       $periode = $claim->created_at->between(Carbon::parse($request->date_start)->startOfDay(), Carbon::parse($request->date_end)->endOfDay());
                       break;
                   case "month":
                       $periode = $claim->created_at->between(Carbon::now()->startOfMonth(),Carbon::now()->endOfMonth());
                       break;
                   case "30days":
                       $periode = $claim->created_at->between(Carbon::now()->subDays(30), Carbon::now());
                       break;
                   case "45days":
                       $periode = $claim->created_at->between(Carbon::now()->subDays(45), Carbon::now());
                       break;
                   case "3months":
                       $periode = $claim->created_at->between(Carbon::now()->subMonth(3), Carbon::now());
                       break;
               }

                if ($periode) {

                    // totalRegistered
                    $totalClaimsRegisteredStatistics->put('total', ($totalClaimsRegisteredStatistics->get('total') + 1));
                    $statistics->put('totalRegistered', $this->incrementTotalRegistered($claim, $statistics->get('totalRegistered')));

                    if (is_null($claim->deleted_at)) { // if claim is not merged

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
                    }

                    // channelsUse
                    $channelsUse->put($claim->requestChannel->name,
                        $this->incrementTotalRegistered($claim, $channelsUse->get($claim->requestChannel->name)));

                    // channelsUse
                    if (!is_null($claim->claimObject)) {
                        $claimObjectsUse->put($claim->claimObject->name,
                            $this->incrementTotalRegistered($claim, $claimObjectsUse->get($claim->claimObject->name)));
                    }

                    // pointOfServicesTargeted
                    if (!is_null($claim->unitTargeted)) {
                        if ($pointOfServicesTargeted->has($claim->unitTargeted->name)) {
                            $pointOfServicesTargeted->put($claim->unitTargeted->name,
                                $this->incrementTotalUnitsTargeted($claim, $pointOfServicesTargeted->get($claim->unitTargeted->name)));
                        }
                    }

                    $institutionsTargeted->put($claim->institutionTargeted->name,
                        $this->incrementTotalRegistered($claim, $institutionsTargeted->get($claim->institutionTargeted->name)));
                }

                $claimerProcessEvolution->put($this->formatMontWithYear($claim->created_at),
                    $this->incrementRegisteredEvolution($claim, $claimerProcessEvolution->get($this->formatMontWithYear($claim->created_at))));

                if (!is_null($claim->activeTreatment)) {

                    $claim->activeTreatment->load($this->getActiveTreatmentRelations());

                    if (!is_null($claim->activeTreatment->transferred_to_unit_at)) {
                        $claimerProcessEvolution->put($this->formatMontWithYear($claim->activeTreatment->transferred_to_unit_at),
                            $this->incrementProcessEvolution($claim
                                , $claimerProcessEvolution->get($this->formatMontWithYear($claim->activeTreatment->transferred_to_unit_at))
                                , 'transferred_to_unit'
                            ));
                    }

                    if (!is_null($claim->activeTreatment->declared_unfounded_at)) {
                        $claimerProcessEvolution->put($this->formatMontWithYear($claim->activeTreatment->declared_unfounded_at),
                            $this->incrementProcessEvolution($claim
                                , $claimerProcessEvolution->get($this->formatMontWithYear($claim->activeTreatment->declared_unfounded_at))
                                , 'unfounded'
                            ));
                    }

                    if (!is_null($claim->activeTreatment->solved_at)) {
                        $claimerProcessEvolution->put($this->formatMontWithYear($claim->activeTreatment->solved_at),
                            $this->incrementProcessEvolution($claim
                                , $claimerProcessEvolution->get($this->formatMontWithYear($claim->activeTreatment->solved_at))
                                , 'treated'
                            ));
                    }

                    // claimerSatisfactionEvolution
                    if (!is_null($claim->activeTreatment->satisfaction_measured_at)) {

                        $claimerProcessEvolution->put($this->formatMontWithYear($claim->activeTreatment->satisfaction_measured_at),
                            $this->incrementProcessEvolution($claim
                                , $claimerProcessEvolution->get($this->formatMontWithYear($claim->activeTreatment->satisfaction_measured_at))
                                , 'measured'
                            ));

                        $claimerSatisfactionEvolution->put($this->formatMontWithYear($claim->activeTreatment->satisfaction_measured_at),
                            $this->incrementClaimerSatisfactionEvolution($claim, $claimerSatisfactionEvolution->get($this->formatMontWithYear($claim->activeTreatment->satisfaction_measured_at))));
                    }

                }

                return $claim;
            });

        $statisticsDashboard = [
            'institutions' => $institutions,
            'statistics' => $statistics,
            'channelsUse' => $channelsUse,
            'claimObjectsUse' => $claimObjectsUse,
            'claimerSatisfactionEvolution' => $claimerSatisfactionEvolution,
            'claimerProcessEvolution' => $claimerProcessEvolution,
            'totalClaimsRegisteredStatistics' => $totalClaimsRegisteredStatistics->get('total'),
            'institutionsTargeted' => $institutionsTargeted,
            'pointOfServicesTargeted' => $pointOfServicesTargeted
        ];

        if($request->has('institution_targeted_id')){

            $statisticsDashboard = Arr::except($statisticsDashboard, 'institutionsTargeted');

        }

        return response()->json($statisticsDashboard, 200);
    }

}
