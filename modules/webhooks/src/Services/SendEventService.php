<?php
namespace Satis2020\Webhooks\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendEventService
{

    public function sendEvent($event, $data, $institutionId)
    {
        $webhook = (new WebhookConfigService())->getByEvent($event, $institutionId);

        if ($webhook != null) {
            try {
                Http::post($webhook->url, $data);
            } catch (\Exception $exception) {
                Log::error("webhook error : " . $exception->getMessage());
            }
        }
    }

}