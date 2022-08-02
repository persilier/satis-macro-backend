<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Satis2020\ServicePackage\Mail\RelanceMail;
use Satis2020\ServicePackage\Models\Claim;

/**
 * Trait RelanceTrait
 * @package Satis2020\ServicePackage\Traits
 */
trait RelanceTrait
{

    /**
     * @param $claimId
     * @param bool $treatment
     * @param bool $my
     * @return Builder|Builder[]|Collection|Model
     */
    protected function getOneRelance($claimId, $treatment = false, $my = false)
    {

        $claim = Claim::with($this->getRelations());

        if ($treatment) {

            $claim->join('treatments', function ($join) {

                $join->on('claims.id', '=', 'treatments.claim_id')
                    ->on('claims.active_treatment_id', '=', 'treatments.id');
            })->select('claims.*');
        }

        if ($my) {

            $claim->where('institution_targeted_id', $this->institution()->id);
        }

        return $claim->findOrFail($claimId);

    }

    /**
     * @param $claimId
     * @param $status
     * @param bool $my
     * @return array
     */
    protected function treatmentAnyMyRelances($claimId, $status, $my = false)
    {

        $identite = null;

        if ($status === 'incomplete' || $status === 'full' || $status === 'transferred_to_targeted_institution') {

            $claim = $this->getOneRelance($claimId, false, $my);
            try {
                if (is_null($claim->createdBy)) {
                    $institution = $claim->institutionTargeted;
                } else {
                    $institution = $claim->createdBy->identite->staff->institution;
                }
                $identite = $this->getInstitutionPilot($institution);
            } catch (\Exception $exception) {
                $identite = null;
            }
        }

        if ($status === 'transferred_to_unit') {

            $claim = $this->getOneRelance($claimId, false, $my);
            try {
                $identite = $this->getIdentitesResponsibleUnit($claim->activeTreatment->responsibleUnit);
            } catch (\Exception $exception) {
                $identite = null;
            }
        }

        if ($status === 'assigned_to_staff') {

            $claim = $this->getOneRelance($claimId, false, $my);
            $identite = $this->getIdentitesResponsibleStaff($claim->activeTreatment->responsibleStaff);

        }

        if ($status === 'treated' || $status === 'validated') {

            $claim = $this->getOneRelance($claimId, false, $my);
            try {
                $identite = $this->getInstitutionPilot($claim->activeTreatment->responsibleStaff->institution);
            } catch (\Exception $exception) {
                $identite = null;
            }
        }

        return [
            'identite' => $identite,
            'claim' => $claim
        ];

    }


    /**
     * @param $identite
     * @param $claim
     */
    protected function notifMailSendDispach($identite, $claim)
    {

        if ($identite instanceof Collection) {
            $identite->each(function ($item) use ($claim) {
                $this->dispatchRelance($item, $claim);
            });

        } else {
            $this->dispatchRelance($identite, $claim);
        }

    }


    protected function dispatchRelance($identite, $claim)
    {
        $mail = new RelanceMail($identite, $claim);
        return Mail::to($identite->email[0])->send($mail);
    }

    /**
     * @return array
     */
    protected function rulesAnyMyRelanceSend()
    {
        return [
            'comment' => 'required|string',
        ];
    }



}