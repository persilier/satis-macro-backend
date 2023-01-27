<?php


namespace Satis2020\ServicePackage\Repositories;


use Satis2020\ServicePackage\Models\ClaimObject;

class ClaimObjectRepository
{
    private $claimObject;

    public function __construct(ClaimObject $claimObject)
    {
        $this->claimObject = $claimObject;
    }

    public function getAllObjectsByCategoryName($categoryName)
    {
        return $this->claimObject->newQuery()->whereHas('claimCategory', function ($query) use ($categoryName) {
            $query->where('name->'.app()->getLocale(), $categoryName);
        })->get();
    }

    public function getTimeLimitByObjectName($objectName)
    {
        return $this->claimObject->newQuery()
            ->where('name->'.app()->getLocale(), $objectName)
            ->firstOrFail()->time_limit;
    }
}