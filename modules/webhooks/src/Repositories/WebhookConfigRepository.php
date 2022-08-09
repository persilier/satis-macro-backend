<?php

namespace Satis2020\Webhooks\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Satis2020\Webhooks\Models\Webhook;

/**
 * Class WebhookConfigRepository
 * @package Satis2020\Escalation\Repositories
 */
class WebhookConfigRepository
{
    /**
     * @var Webhook
     */
    private $webhookConfig;

    /**
     * UserRepository constructor.
     */
    public function __construct()
    {
        $this->webhookConfig = new Webhook;
    }

    public function getAll($institutionId)
    {
        return $this->webhookConfig
            ->newQuery()
            ->where('institution_id',$institutionId)
            ->get();
    }

    public function getByEvent($event,$institutionId,$webhookId=null)
    {
        return $this->webhookConfig
            ->newQuery()
            ->when($webhookId!=null,function ($query) use ($webhookId){
                $query->where('id','!=',$webhookId);
            })
            ->where('institution_id',$institutionId)
            ->where('event',$event)
            ->first();
    }

    public function getById($id)
    {
        return $this->webhookConfig
            ->newQuery()
            ->findOrFail($id);
    }

    /**
     * @param $data
     * @return Builder|Model
     */
    public function store($data)
    {
        return $this->webhookConfig
            ->newQuery()
            ->create($data)->refresh();
    }

    /**
     * @param $data
     * @param $webhookConfigId
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function update($data,$webhookConfigId)
    {
        $webhookConfig = $this->getById($webhookConfigId);
        $webhookConfig->update($data);

        return $webhookConfig->refresh();
    }

    /**
     * @param $webhookConfigId
     * @return Builder|Builder[]|Collection|Model|null
     * @throws \Exception
     */
    public function remove($webhookConfigId)
    {
        $webhookConfig = $this->getById($webhookConfigId);
        $webhookConfig->delete();

        return $webhookConfig;
    }


}