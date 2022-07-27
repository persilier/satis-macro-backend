<?php

namespace Satis2020\ServicePackage\Services;

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
    ){
        $this->claimRepository = $claimRepository;
        $this->claimObjectRepository = $claimObjectRepository;
    }

    /***
     * @return \Illuminate\Support\Collection
     */
    public function getAllClaimClassification()
    {
        $claims = collect();

        $this->claimRepository->getAllClaimsWithRelations()->map(function ($item) use ($claims) {
            return $claims->push([
                "reference" => $item->reference,
                "description" => $item->description,
                "object" => optional($item->claimObject)->name,
                "category" => optional(optional(optional($item->claimObject)->claimCategory))->name,
            ]);
        })->all();

        return $claims;
    }

    /***
     * @param $categoryName
     * @return \Illuminate\Support\Collection
     */
    public function getAllObjectsByCategoryName($categoryName)
    {
        return $this->claimObjectRepository->getAllObjectsByCategoryName($categoryName)->pluck('name');
    }
}