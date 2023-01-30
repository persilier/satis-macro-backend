<?php


namespace Satis2020\ServicePackage\Traits;


use Satis2020\ServicePackage\Repositories\NotificationProofRepository;
use Satis2020\ServicePackage\Services\NotificationProof\NotificationProofService;

trait NotificationProof
{

    protected function storeProof($data,$institution_id)
    {
        $service = new NotificationProofService(app(NotificationProofRepository::class));
        $service->store($institution_id,$data);
    }
}