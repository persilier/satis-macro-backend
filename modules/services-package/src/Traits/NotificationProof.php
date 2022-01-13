<?php


namespace Satis2020\ServicePackage\Traits;


use Satis2020\ServicePackage\Repositories\NotificationProofRepository;
use Satis2020\ServicePackage\Services\ActivityLog\NotificationProofService;

trait NotificationProof
{

    protected function storeProof($claim,$data)
    {

        $institution =  is_null($claim->createdBy) ? $claim->institutionTargeted:  $this->claim->createdBy->institution;
        $service = new NotificationProofService(app(NotificationProofRepository::class) );
        $service->store($institution->id,$data);
    }
}