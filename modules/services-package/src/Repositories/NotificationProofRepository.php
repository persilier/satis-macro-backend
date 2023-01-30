<?php

namespace Satis2020\ServicePackage\Repositories;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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
        return $this->notificationProof->newQuery()
            ->create($data);
    }

    /***
     * @param int $pagination
     * @return mixed
     */
    public function getAll($pagination)
    {
        return $this->notificationProof->newQuery()
            ->with("institution", "to")
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
            ->with('to')
            ->where('institution_id', $institutionId)
            ->latest()
            ->get();
    }

    /***
     * @param $institutionId
     * @param $request
     * @param $paginate
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByInstitutionAndFilter($institutionId, $request, $paginate)
    {
        $query = $this->notificationProof->newQuery()
            ->with('to')
            ->where('institution_id', $institutionId);

        if (!is_null($request)) {
            if ($request->has('channel')) {
                $query = $query->where('channel', $request->channel);
            }

            if ($request->has('to')) {
                $query = $query->whereHas("to", function ($query) use ($request) {
                    $query->where('firstname', 'LIKE', '%' . $request->to . '%')
                        ->orWhere('lastname', 'LIKE', '%' . $request->to . '%')
                        ->orWhere('telephone', 'LIKE', '%' . $request->to . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->to . '%');
                });
            }

            if ($request->has('date_start') && $request->has('date_end')) {
                $query = $query->whereBetween('sent_at',
                    [Carbon::parse($request->date_start)->startOfDay(), Carbon::parse($request->date_end)->endOfDay()]
                );
            }
        }

        return $query->paginate($paginate);
    }





    /**
     * @param $institutionId
     * @param $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getByInstitutionAndFilterToExport($institutionId, $request)
    {
        $query = $this->notificationProof->newQuery()
            ->with('to')
            ->where('institution_id', $institutionId);
        if (!is_null($request)) {
            if ($request->has('date_start') && $request->has('date_end')) {
                $query = $query->whereBetween('sent_at',
                    [ Carbon::parse($request->date_start)->startOfDay(), Carbon::parse($request->date_end)->endOfDay()]
                );
            }
        }

        return $query->get();
    }
    /***
     * @param $request
     * @param $paginate
     * @return Builder[]|Collection
     */
    public function getAllAndFilter($request, $paginate)
    {
        $query = $this->notificationProof->with('institution', 'to');
        if (!is_null($request)) {
            if ($request->has('institution_id')) {
                $query = $query->where('institution_id', $request->institution_id);
            }

            if ($request->has('channel')) {
                $query = $query->where('channel', $request->channel);
            }

            if ($request->has('to')) {
                $query = $query->whereHas("to", function ($query) use ($request) {
                    $query->where('firstname', 'LIKE', '%' . $request->to . '%')
                        ->orWhere('lastname', 'LIKE', '%' . $request->to . '%')
                        ->orWhere('telephone', 'LIKE', '%' . $request->to . '%')
                        ->orWhere('email', 'LIKE', '%' . $request->to . '%');
                });
            }

            if ($request->has('date_start') && $request->has('date_end')) {
                $query = $query->whereBetween('sent_at',
                    [Carbon::parse($request->date_start)->startOfDay(), Carbon::parse($request->date_end)->endOfDay()]
                );
            }
        }

        return $query->get();
    }

}