<?php

namespace Satis2020\ServicePackage\Services;

use Illuminate\Support\Collection;
use Satis2020\ServicePackage\Repositories\ClaimObjectRepository;
use Satis2020\ServicePackage\Repositories\ClaimRepository;

class ClaimClassificationService
{
    /***
     * @var ClaimRepository
     */
    protected $claimRepository;
    protected $claimObjectRepository;

    public function __construct(
        ClaimRepository $claimRepository,
        ClaimObjectRepository $claimObjectRepository
    )
    {
        $this->claimRepository = $claimRepository;
        $this->claimObjectRepository = $claimObjectRepository;
    }

    /***
     * @return Collection
     */
    public function getAllClaimClassification()
    {
        return $this->formatClaimsToSend($this->claimRepository->getAllClaimsWithRelations());
    }

    /***
     * @return Collection
     */
    public function getAllClaimClassificationRevoked()
    {
        return $this->formatClaimsToSend($this->claimRepository->getAllClaimsRevokedWithRelations());
    }

    public function formatClaimsToSend($data)
    {
        $claims = collect();

         $data->map(function ($item) use ($claims) {
            return $claims->push([
                "reference" => $item->reference,
                "description" => $item->description,
                "object" => optional($item->claimObject)->name,
                "rejected_reason" =>optional($item->activeTreatment)->rejected_reason,
                "rejected_at" => optional($item->activeTreatment)->rejected_at,
                "category" => optional(optional(optional($item->claimObject)->claimCategory))->name,
            ]);
        })->all();

         return $claims;
    }

    /***
     * @param $categoryName
     * @return Collection
     */
    public function getAllObjectsByCategoryName($categoryName)
    {
        return $this->claimObjectRepository->getAllObjectsByCategoryName($categoryName)->pluck('name');
    }

    /***
     * @param $objectName
     * @return Collection
     */
    public function getTimeLimitByObjectName($objectName)
    {
        return $this->claimObjectRepository->getTimeLimitByObjectName($objectName);
    }
}