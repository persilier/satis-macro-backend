<?php

namespace Satis2020\ServicePackage\Repositories;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Satis2020\ServicePackage\Models\NotificationProof;

class NotificationProofRepository
{
    /***
     * @var NotificationProof
     */
    protected $notificationProof;

    /***
     * @var array
     */
    protected $with = [];

    public function __construct(NotificationProof $notificationProof)
    {
        $this->notificationProof = $notificationProof;
    }

    /***
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->notificationProof->newQuery()->create($data);
    }

    /***
     * @param int $pagination
     * @return mixed
     */
    public function getAll($pagination)
    {
        return $this->notificationProof->newQuery()
            ->with("institution")
            ->get();
    }

    /***
     * @param $institutionId
     * @param null $paginate
     * @return mixed
     */
    public function getByInstitution($institutionId, $paginate)
    {
        return $this->notificationProof->newQuery()
            ->where('institution_id', $institutionId)
            ->latest()
            ->get();
    }

    /***
     * @param $institutionId
     * @param $request
     * @param $paginate
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getByInstitutionAndFilter($institutionId, $request ,$paginate)
    {
         $query = $this->notificationProof->newQuery()
                            ->where('institution_id', $institutionId);
         if (!is_null($request)) {

             if ($request->has('channel')) {
                 $query = $query->where('channel', $request->channel);
             }

             if ($request->has('to')) {
                 $query = $query->where('to', 'LIKE','%'.$request->to.'%');
             }

             if ($request->has('date_start') && $request->has('date_end')) {
                 $query = $query->whereBetween('sent_at',
                     [ Carbon::parse($request->date_start)->startOfDay(), Carbon::parse($request->date_end)->endOfDay()]
                 );
             }
         }

        return $query->get();
    }

    /***
     * @param $institutionId
     * @param $request
     * @param $paginate
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllAndFilter($request ,$paginate)
    {
         $query = $this->notificationProof->with('institution');
         if (!is_null($request)) {
             if ($request->has('institution_id')) {
                 $query = $query->where('institution_id', $request->institution_id);
             }

             if ($request->has('channel')) {
                 $query = $query->where('channel', $request->channel);
             }

             if ($request->has('to')) {
                 $query = $query->where('to', 'LIKE','%'.$request->to.'%');
             }

             if ($request->has('date_start') && $request->has('date_end')) {
                 $query = $query->whereBetween('sent_at',
                     [ Carbon::parse($request->date_start)->startOfDay(), Carbon::parse($request->date_end)->endOfDay()]
                 );
             }
         }

        return $query->get();
    }

}