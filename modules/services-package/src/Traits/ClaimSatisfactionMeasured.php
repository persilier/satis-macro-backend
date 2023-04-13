<?php

namespace Satis2020\ServicePackage\Traits;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Satis2020\ServicePackage\Models\Claim;
use Illuminate\Database\Eloquent\Collection;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Exceptions\CustomException;

/**
 * Trait ClaimSatisfactionMeasured
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimSatisfactionMeasured
{

    /**
     * @param string $status
     * @param $statusColumn
     * @return Builder
     */
    protected  function getClaim($status = 'validated', $statusColumn = "status")
    {

        $claims = Claim::with($this->getRelations())->join('treatments', function ($join) {
            $join->on('claims.id', '=', 'treatments.claim_id')
                ->on('claims.active_treatment_id', '=', 'treatments.id')->where('treatments.responsible_staff_id', '!=', NULL);
        })->where(function ($query)  use ($statusColumn, $status) {
            $query->where("claims.$statusColumn", $status)
                ->when($status == Claim::CLAIM_ARCHIVED, function ($queryEscalation) use ($status) {
                    $queryEscalation->orWhere("claims.escalation_status", $status)
                        ->orWhere("claims.escalation_status", Claim::CLAIM_CLOSED)
                        ->orWhere("claims.escalation_status", Claim::CLAIM_RESOLVED);
                });
        })->select('claims.*');
        // ->where("claims.$statusColumn", $status)->select('claims.*')
        // if ($status == Claim::CLAIM_ARCHIVED) {
        //     $claims->orWhere("claims.escalation_status", $status)->select('claims.*');
        // }
        return $claims;
    }


    /**
     * @param $request
     * @return mixed
     */
    protected  function rules($request)
    {

        $data['is_claimer_satisfied'] = ['required', 'boolean'];
        $data['unsatisfaction_reason'] = ['nullable', Rule::requiredIf((!$request->is_claimer_satisfied)), 'string'];
        $data['note'] = 'nullable|integer|min:1|max:5';

        return $data;
    }

    /**
     * @return array
     */
    protected function getRelations()
    {
        return Constants::getClaimRelations();
    }


    /**
     * @param string $status
     * @param bool $paginate
     * @param int $paginationSize
     * @param null $key
     * @param string $statusColumn
     * @return mixed
     */

    protected function getAllMyClaim($status = 'validated', $paginate = false, $paginationSize = 10, $key = null, $statusColumn = 'status')
    {

        return $paginate
            ? $this->getClaim($status, $statusColumn)
            ->where('institution_targeted_id', $this->institution()->id)
            ->when($key, function (Builder $query1) use ($key) {
                $query1->where(function ($qy) use ($key) {
                    $qy->where('reference', 'LIKE', "%$key%")
                        ->orWhereHas("claimer", function ($query2) use ($key) {
                            $query2->where('firstname', 'LIKE', "%$key%")
                                ->orWhere('lastname', 'LIKE', "%$key%")
                                ->orwhereJsonContains('telephone', $key)
                                ->orwhereJsonContains('email', $key);
                        })->orWhereHas("claimObject", function ($query3) use ($key) {
                            $query3->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                        })->orWhereHas("unitTargeted", function ($query4) use ($key) {
                            $query4->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                        });
                });
            })->paginate($paginationSize)
            : $this->getClaim($status)->get()->filter(function ($item) {
                return ($this->institution() && $item->activeTreatment->responsibleStaff && $this->institution()->id === $item->activeTreatment->responsibleStaff->institution_id);
            })->values();
    }
    /**
     * @param string $status
     * @param bool $paginate
     * @param int $paginationSize
     * @param null $key
     * @param string $statusColumn
     * @return mixed
     */

    protected function getAllForSatisfactionMyClaim($status = 'validated', $paginate = false, $paginationSize = 10, $key = null, $statusColumn = 'status')
    {

        return $paginate
            ? $this->getClaim($status, $statusColumn)
            ->where('institution_targeted_id', $this->institution()->id)
            ->whereHas('activeTreatment', function ($queryStaff2) {
                $queryStaff2->where('satisfaction_responsible_staff_id', $this->staff()->id)
                    ->whereNotNull('transfered_to_satisfaction_responsible_at');
            })
            ->when($key, function (Builder $query1) use ($key) {
                $query1->where('reference', 'LIKE', "%$key%")
                    ->orWhereHas("claimer", function ($query2) use ($key) {
                        $query2->where('firstname', 'LIKE', "%$key%")
                            ->orWhere('lastname', 'LIKE', "%$key%")
                            ->orwhereJsonContains('telephone', $key)
                            ->orwhereJsonContains('email', $key);
                    })->orWhereHas("claimObject", function ($query3) use ($key) {
                        $query3->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                    })->orWhereHas("unitTargeted", function ($query4) use ($key) {
                        $query4->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                    });
            })->paginate($paginationSize)
            : $this->getClaim($status)->get()->filter(function ($item) {
                return ($this->institution() && $item->activeTreatment->responsibleStaff && $this->institution()->id === $item->activeTreatment->responsibleStaff->institution_id);
            })->values();
    }

    /**
     * @param string $status
     * @param bool $paginate
     * @param int $paginationSize
     * @param null $key
     * @param string $statusColumn
     * @return mixed
     */

    protected function getArchivedMyClaim($status = 'validated', $paginate = false, $paginationSize = 10, $key = null, $statusColumn = 'status', $institutionId =null)
    {

        return $paginate
            ? $this->getClaim($status, $statusColumn)
            ->where('institution_targeted_id', $this->institution()->id)
            ->when($key, function (Builder $query1) use ($key) {
                $query1->where('reference', 'LIKE', "%$key%")
                    ->orWhereHas("claimer", function ($query2) use ($key) {
                        $query2->where('firstname', 'LIKE', "%$key%")
                            ->orWhere('lastname', 'LIKE', "%$key%")
                            ->orwhereJsonContains('telephone', $key)
                            ->orwhereJsonContains('email', $key);
                    })->orWhereHas("claimObject", function ($query3) use ($key) {
                        $query3->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                    })->orWhereHas("unitTargeted", function ($query4) use ($key) {
                        $query4->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                    });
            })->paginate($paginationSize)
            : $this->getClaim($status)->get()->filter(function ($item) {
                return ($this->institution() && $item->activeTreatment->responsibleStaff && $this->institution()->id === $item->activeTreatment->responsibleStaff->institution_id);
            })->values();
    }


    /**
     * @param int $paginationSize
     * @param null $key
     * @param null $type
     * @return mixed
     */

    protected function getAllMyUnsatisfiedClaim($paginationSize = 10, $key = null, $type = null)
    {
        $claims = $this->getClaim(Claim::CLAIM_UNSATISFIED)
            ->where('institution_targeted_id', $this->institution()->id)
            ->whereNull('escalation_status')
            ->whereHas("activeTreatment", function ($builder) {
                $builder->where('is_claimer_satisfied', false);
            })->whereNull('treatment_board_id');

        switch ($type) {
            case 'claimObject':
                $claims->whereHas("claimObject", function ($query) use ($key) {
                    $query->where("name->" . App::getLocale(), 'LIKE', "%$key%");
                });
                break;
            case 'claimer':
                $claims->whereHas("claimer", function ($query) use ($key) {
                    $query->where('firstname', 'like', "%$key%")
                        ->orWhere('lastname', 'like', "%$key%")
                        ->orwhereJsonContains('telephone', $key)
                        ->orwhereJsonContains('email', $key);
                });
                break;
            default:
                $claims->where('reference', 'LIKE', "%$key%");
                break;
        }

        return $claims->paginate($paginationSize);
    }


    /**
     * @param $claim
     * @param string $status
     * @param string $statusColum
     * @return Builder|Builder[]|Collection|Model
     * @throws CustomException
     */
    protected function getOneMyClaim($claim, $status = 'validated', $statusColum = "status")
    {

        $claim = $this->getClaim($status, $statusColum)->findOrFail($claim);

        if ($claim->activeTreatment->responsibleStaff->institution_id !== $this->institution()->id) {

            throw new CustomException(__('messages.cant_get_claim', [], getAppLang()));
        }
        return $claim;
    }
}
