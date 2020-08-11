<?php

namespace Satis2020\ServicePackage\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Class SendMail
 * @package Satis2020\ServicePackage\Jobs
 */
class SendMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mailable;

    /**
     * Create a new job instance.
     *
     * @param Mailable $mailable
     */
    public function __construct(Mailable $mailable)
    {
        $this->mailable = $mailable;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->mailable->user->identite->email)->send($this->mailable);
    }
}
