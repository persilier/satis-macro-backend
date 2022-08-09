<?php
namespace Satis2020\Webhooks\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendEventService
{

    public function sendEvent($event, $data, $institutionId)
    {
        $url = (new WebhookConfigService())->getByEvent($event, $institutionId);

        if ($url != null) {
            try {
                Http::post($url, $data);
            } catch (\Exception $exception) {
                Log::error("webhook error : " . $exception->getMessage());
            }
        }
    }

}