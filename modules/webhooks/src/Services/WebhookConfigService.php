<?php


namespace Satis2020\Webhooks\Services;


use Illuminate\Http\Request;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\Webhooks\Repositories\WebhookConfigRepository;

class WebhookConfigService
{

    use DataUserNature;

    /**
     * @var WebhookConfigRepository
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new WebhookConfigRepository;
    }

    public function getAll()
    {
        return $this->repository->getAll($this->institution()->id);
    }


    public function getByEvent($event, $institutionId, $webhookId = null)
    {
        return $this->repository->getByEvent($event, $institutionId, $webhookId);
    }

    public function getById($id)
    {
        return $this->repository->getById($id);
    }

    public function store(Request $request)
    {
        return $this->repository->store($request->only([
            'name',
            'event',
            'url',
            'institution_id',
        ]));
    }


    public function update(Request $request)
    {

        return $this->repository->update($request->only([
            'name',
            'event',
            'url',
            'institution_id',
        ]), $request->id);
    }


    public function remove($webhookConfigId)
    {
        return $this->repository->remove($webhookConfigId);
    }

    public function getWebhookUrl($event, $institutionId)
    {
        return $this->repository->getByEvent($event, $institutionId) != null ?
            $this->repository->getByEvent($event, $institutionId)->urls
            : null;
    }
}