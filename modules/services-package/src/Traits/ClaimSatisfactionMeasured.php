<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Claim;

/**
 * Trait ClaimSatisfactionMeasured
 * @package Satis2020\ServicePackage\Traits
 */
trait ClaimSatisfactionMeasured
{

    /**
     * @param string $status
     * @return Builder
     */
    protected  function getClaim($status = 'validated'){

        return $claims = Claim::with($this->getRelations())->join('treatments', function ($join){
            $join->on('claims.id', '=', 'treatments.claim_id')
                ->on('claims.active_treatment_id', '=', 'treatments.id')->where('treatments.responsible_staff_id', '!=', NULL);
        })->where('claims.status', $status)->select('claims.*');

    }


    /**
     * @param $request
     * @return mixed
     */
    protected  function rules($request){
        
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
        return [
            'claimObject.claimCategory',
            'claimer',
            'relationship',
            'accountTargeted',
            'institutionTargeted',
            'unitTargeted',
            'requestChannel',
            'responseChannel',
            'amountCurrency',
            'createdBy.identite',
            'completedBy.identite',
            'files',
            'activeTreatment.satisfactionMeasuredBy.identite',
            'activeTreatment.responsibleStaff.identite',
            'activeTreatment.assignedToStaffBy.identite',
            'activeTreatment.responsibleUnit'
        ];
    }


    /**
     * @param string $status
     * @param bool $paginate
     * @param int $paginationSize
     * @param null $key
     * @return array
     */

    protected function getAllMyClaim($status = 'validated',$paginate = false, $paginationSize = 10,$key=null){
        return $paginate
            ?$this->getClaim($status)
                ->where('institution_targeted_id', $this->institution()->id)
                ->when($key,function (Builder $query1) use ($key) {
                    $query1->where('reference' , 'LIKE', "%$key%")
                        ->orWhereHas("claimer",function ($query2) use ($key){
                            $query2->where('firstname' , 'LIKE', "%$key%")
                                ->orWhere('lastname' , 'LIKE', "%$key%")
                                ->orwhereJsonContains('telephone', $key)
                                ->orwhereJsonContains('email', $key);
                        })->orWhereHas("claimObject",function ($query3) use ($key){
                            $query3->where("name->".App::getLocale(), 'LIKE', "%$key%");
                        })->orWhereHas("unitTargeted",function ($query4) use ($key){
                            $query4->where("name->".App::getLocale(), 'LIKE', "%$key%");
                        });
                })->paginate($paginationSize)

            :$this->getClaim($status)->get()->filter(function ($item){
                return ($item->activeTreatment->responsibleStaff!=null && $this->institution()->id === $item->activeTreatment->responsibleStaff->institution_id);
            })->values();

    }



    /**
     * @param string $status
     * @param bool $paginate
     * @param int $paginationSize
     * @param null $key
     * @return mixed
     */

    protected function getAllMyUnsatisfiedClaim($paginationSize = 10,$key=null){
       $claims = $this->getClaim(Claim::CLAIM_VALIDATED)
                ->where('institution_targeted_id', $this->institution()->id)
                ->whereHas("activeTreatment",function ($builder){
                    $builder->where('is_claimer_satisfied',false);
                })
               ->when($key,function (Builder $query1) use ($key) {
                    $query1->where('reference' , 'LIKE', "%$key%")
                        ->orWhereHas("claimer",function ($query2) use ($key){
                            $query2->where('firstname' , 'LIKE', "%$key%")
                                ->orWhere('lastname' , 'LIKE', "%$key%")
                                ->orwhereJsonContains('telephone', $key)
                                ->orwhereJsonContains('email', $key);
                        })->orWhereHas("claimObject",function ($query3) use ($key){
                            $query3->where("name->".App::getLocale(), 'LIKE', "%$key%");
                        })->orWhereHas("unitTargeted",function ($query4) use ($key){
                            $query4->where("name->".App::getLocale(), 'LIKE', "%$key%");
                        });
                })->paginate($paginationSize);

                return $claims;
            /*:$this->getClaim($status)->get()->filter(function ($item){
                return (
                    $item->activeTreatment->responsibleStaff!=null &&
                    $this->institution()->id === $item->activeTreatment->responsibleStaff->institution_id);
            })->values();*/

    }


    /**
     * @param $claim
     * @param $status
     * @return Builder|Builder[]|Collection|Model
     * @throws CustomException
     */
    protected function getOneMyClaim($claim, $status = 'validated'){

        $claim = $this->getClaim($status)->findOrFail($claim);

        if($claim->activeTreatment->responsibleStaff->institution_id !== $this->institution()->id){

            throw new CustomException(__('messages.cant_get_claim',[],getAppLang()));

        }

        return $claim;

    }


}
