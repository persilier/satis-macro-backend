<?php

namespace Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Satis2020\ActivePilot\Http\Controllers\ConfigurationPilot\ConfigurationPilotTrait;

class ProcessNotifyAllActivePilot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ConfigurationPilotTrait;

    public $claim, $severityLevel, $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($claim, $severityLevel, $user_id)
    {
        $this->claim = $claim;
        $this->severityLevel = $severityLevel;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->notifyAllPilotAfterRegisterClaim($this->claim, $this->severityLevel, $this->user_id);
    }
}
