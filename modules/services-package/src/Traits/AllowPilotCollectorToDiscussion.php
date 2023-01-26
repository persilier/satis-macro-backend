<?php

namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Models\Metadata;


trait AllowPilotCollectorToDiscussion
{
    public function getAllowPilotCollectorToDiscussionConfiguration()
    {
        $metadata = json_decode(Metadata::where('name', 'allow-pilot-collector-to-discussion')->first()->data);
        $parameters = [
            "allow_pilot" => (int) $metadata->allow_pilot,
            "allow_collector" => (int) $metadata->allow_collector,
        ];
        return $parameters;
    }
    public function setPermissionForBi
}
