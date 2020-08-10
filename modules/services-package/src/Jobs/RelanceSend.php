<?php

namespace Satis2020\ServicePackage\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;


/**
 * Class RelanceSendMail
 * @package Satis2020\ServicePackage\Jobs
 */
class RelanceSend implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $responses;

    /**
     * Create a new job instance.
     *
     * @param $responses
     */
    public function __construct($responses)
    {
        $this->responses = $responses;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // traitement et envoie des notifications de relances

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
