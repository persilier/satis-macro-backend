<?php

namespace Satis2020\ServicePackage\Repositories;

use Carbon\Carbon;
use Satis2020\ServicePackage\Models\Activity;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;

class ActivityLogRepository
{
    /***
     * @var Activity
     */
    protected $activity;

    /***
     * @var array
     */
    protected $with = [];

    public function __construct(Activity $activity)
    {
        $this->activity = $activity;
    }

    /***
     * @return mixed
     */
    public function getAll()
    {
        return $this->activity->get();
    }

    /***
     * @param $id
     * @return mixed
     */
    public function getById($id) {
        return $this->activity->find($id);
    }

    /***
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->activity->create($data);
    }


    public function update($data, $id) {
        $activity = $this->getById($id);
        $activity->update($data);
        return $activity->refresh();
    }

    /***
     * @param $institutionId
     * @param null $paginate
     * @return mixed
     */
    public function getByInstitution($institutionId, $paginate)
    {
        return $this->activity->with('institution', 'causer.identite', 'subject')
                            ->where('institution_id', $institutionId)
                            ->latest()
                            ->paginate($paginate);
    }

    /***
     * @param $institutionId
     * @param $request
     * @param $paginate
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByInstitutionFilters($institutionId, $request ,$paginate)
    {
         $query = $this->activity->with('institution', 'causer.identite', 'subject')
                            ->where('institution_id', $institutionId);
         if (!is_null($request)) {

             if ($request->has('causer_id')) {
                 $query = $query->where('causer_id', $request->causer_id);
             }

             if ($request->has('log_action')) {
                 $query = $query->where('log_action', $request->log_action);
             }

             if ($request->has('date_start') && $request->has('date_end')) {
                 $query = $query->whereBetween('created_at',
                     [ Carbon::parse($request->date_start)->startOfDay(), Carbon::parse($request->date_end)->endOfDay()]
                 );
             }
         }

        return $query->latest()->paginate($paginate);
    }

    /**
     * @param $user_id
     * @param $action_type
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function getLastLogByUserAndAction($user_id, $action_type)
    {
        return $this->activity->newQuery()
            ->where("causer_id",$user_id)
            ->where('log_action',$action_type)
            ->orderByDesc('created_at')
            ->first();
    }



}