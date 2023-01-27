<?php

namespace Satis2020\ServicePackage\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Satis2020\ServicePackage\Mail\PdfRegulatoryStateReportingMail;
use Satis2020\ServicePackage\Mail\PdfReportingMail;
use Exception;


/**
 * Class PdfReportingSendMail
 * @package Satis2020\ServicePackage\Jobs
 */
class PdfRegulatoryStateReportingSendMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $details;

    /**
     * Create a new job instance.
     *
     * @param $details
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        foreach ($this->details['email'] as $recipient) {
            Mail::to($recipient)->send(new PdfRegulatoryStateReportingMail($this->details));

        }

        $this->details['reportingTask']->cronTasks()->create();
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
