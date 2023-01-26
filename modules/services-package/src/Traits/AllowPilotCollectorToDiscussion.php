<?php

namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Models\Role;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Traits\ActivePilot;
use Satis2020\ServicePackage\Traits\Notification;


trait AllowPilotCollectorToDiscussion
{
    use ActivePilot;

    public function getAllowPilotCollectorToDiscussionConfiguration()
    {
        $metadata = json_decode(Metadata::where('name', 'allow-pilot-collector-to-discussion')->first()->data);
        $parameters = [
            "allow_pilot" => (int) $metadata->allow_pilot,
            "allow_collector" => (int) $metadata->allow_collector,
        ];
        return $parameters;
    }
    public function setPilotPermissionForDiscussion($institution)
    {
        $config  = $this->getAllowPilotCollectorToDiscussionConfiguration()["allow_pilot"];
        $pilot_role = Role::where('name', $this->getPilotRoleNameByInstitution($institution))->first();
        if ($config == 1) {
            $pilot_role->givePermissionTo([
                'store-discussion', 'add-discussion-contributor', 'remove-discussion-contributor', 'destroy-discussion'
            ]);
        }
        else if ($config == 0) {
            $pilot_role->revokePermissionTo([
                'store-discussion', 'add-discussion-contributor', 'remove-discussion-contributor', 'destroy-discussion'
            ]);
        }
    }

    public function setCollectorPermissionForDiscussion($institution)
    {
        $config  = $this->getAllowPilotCollectorToDiscussionConfiguration()["allow_collector"];
        $collector_role = Role::where('name', 'collector-filial-pro')->first();
        if ($config == 1) {
            $collector_role->givePermissionTo([
                'contribute-discussion', 'list-my-discussions'
            ]);
        }
        else if ($config == 0) {
            $collector_role->revokePermissionTo([
                'contribute-discussion', 'list-my-discussions'
            ]);
        }
    }
}
