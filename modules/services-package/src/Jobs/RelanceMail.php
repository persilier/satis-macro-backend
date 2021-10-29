<?php

namespace Satis2020\ServicePackage\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\MonitoringClaim;
use Satis2020\ServicePackage\Traits\Notification;
use Satis2020\ServicePackage\Traits\RelanceTrait;


/**
 * Class RelanceMail
 * @package Satis2020\ServicePackage\Jobs
 */
class RelanceMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, RelanceTrait, MonitoringClaim, Notification, DataUserNature;

    protected $claim;
    protected $my;
    protected $request;

    /**
     * Create a new job instance.
     * @param $request
     * @param $claim
     * @param $my
     */
    public function __construct($request, $claim, $my)
    {
        $this->request = $request->comment;
        $this->claim = $claim;
        $this->my = $my;
    }


    /**
     * @return bool
     */
    public function handle()
    {

        $resultats = $this->treatmentAnyMyRelances($this->claim->id, $this->claim->status, $this->my);

        if(!is_null($resultats['identite'])){
             $this->notifMailSendDispach($resultats['identite'], $resultats['claim']);
        }

        return true;

    }

    /**
     * Handle a job failure.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        // Send user notification of failure, etc...
    }

}
