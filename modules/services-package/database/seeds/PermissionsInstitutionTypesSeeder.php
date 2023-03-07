<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\File;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Staff;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\UnitType;
use Satis2020\ServicePackage\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionsInstitutionTypesSeeder extends Seeder
{

    public function addInstitutionTypeToPermission($permission, $institutionType)
    {
        $institution_types = is_null($permission->institution_types)
            ? []
            : json_decode($permission->institution_types);

        if (!in_array($institutionType, $institution_types)) {
            array_push($institution_types, $institutionType);
        }

        $permission->update(['institution_types' => json_encode($institution_types)]);
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $holdingPermissions = [
            'list-any-institution', 'store-any-institution', 'update-any-institution', 'destroy-any-institution', 'show-any-institution',
            'list-category-client', 'store-category-client', 'update-category-client', 'destroy-category-client', 'show-category-client',
            'list-channel', 'store-channel', 'update-channel', 'destroy-channel', 'show-channel',
            'list-claim-awaiting-assignment', 'show-claim-awaiting-assignment', 'merge-claim-awaiting-assignment',
            'store-claim-against-any-institution',
            'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
            'list-claim-awaiting-validation-my-institution', 'show-claim-awaiting-validation-my-institution', 'validate-treatment-my-institution',
            'list-claim-category', 'store-claim-category', 'update-claim-category', 'destroy-claim-category', 'show-claim-category',
            'list-claim-object', 'store-claim-object', 'update-claim-object', 'destroy-claim-object', 'show-claim-object',
            'update-claim-object-requirement',
            'list-any-claim-archived', 'show-any-claim-archived',
            'list-satisfaction-measured-my-claim', 'update-satisfaction-measured-my-claim',
            'list-client-from-any-institution', 'store-client-from-any-institution', 'update-client-from-any-institution', 'destroy-client-from-any-institution', 'show-client-from-any-institution',
            'show-mail-parameters', 'update-mail-parameters',
            'show-sms-parameters', 'update-sms-parameters',
            'list-currency', 'store-currency', 'update-currency', 'destroy-currency', 'show-currency',
            'show-dashboard-data-all-institution', 'show-dashboard-data-my-institution', 'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
            'list-my-discussions', 'store-discussion', 'destroy-discussion', 'list-discussion-contributors', 'add-discussion-contributor', 'remove-discussion-contributor', 'contribute-discussion',
            'list-faq-category', 'store-faq-category', 'update-faq-category', 'destroy-faq-category', 'show-faq-category',
            'list-message-apis', 'store-message-apis', 'update-message-apis', 'destroy-message-apis', 'update-institution-message-api', 'update-my-institution-message-api',
            'list-monitoring-claim-any-institution',
            'list-position', 'store-position', 'update-position', 'destroy-position', 'show-position',
            'list-unit-type', 'store-unit-type', 'update-unit-type', 'destroy-unit-type', 'show-unit-type',
            'list-any-unit', 'store-any-unit', 'update-any-unit', 'destroy-any-unit', 'show-any-unit',
            'list-category-client-from-my-institution', 'store-category-client-from-my-institution', 'update-category-client-from-my-institution', 'destroy-category-client-from-my-institution', 'show-category-client-from-my-institution',
            'update-notifications',
            'list-performance-indicator', 'store-performance-indicator', 'update-performance-indicator', 'destroy-performance-indicator', 'show-performance-indicator', 'edit-performance-indicator',
            'update-processing-circuit-any-institution',
            'list-reporting-claim-any-institution',
            'list-severity-level', 'update-severity-level', 'show-severity-level',
            'list-staff-from-any-unit', 'store-staff-from-any-unit', 'update-staff-from-any-unit', 'destroy-staff-from-any-unit', 'show-staff-from-any-unit', 'edit-staff-from-any-unit',
            'transfer-claim-to-circuit-unit',
            'transfer-claim-to-targeted-institution',
            'list-claim-incomplete-against-any-institution', 'show-claim-incomplete-against-any-institution', 'update-claim-incomplete-against-any-institution',
            'list-account-type', 'show-account-type', 'update-account-type', 'store-account-type', 'destroy-account-type',
            'list-user-any-institution', 'show-user-any-institution', 'store-user-any-institution',
            'list-delai-qualification-parameters', 'show-delai-qualification-parameters', 'store-delai-qualification-parameters', 'destroy-delai-qualification-parameters',
            'list-delai-treatment-parameters', 'show-delai-treatment-parameters', 'store-delai-treatment-parameters', 'destroy-delai-treatment-parameters',
            'update-components-parameters',
            'list-any-institution-type-role', 'show-any-institution-type-role', 'store-any-institution-type-role', 'update-any-institution-type-role', 'destroy-any-institution-type-role',
            'history-list-create-claim',
            'history-list-treat-claim',
            'update-active-pilot',
            'unfounded-claim-awaiting-assignment',
            'update-recurrence-alert-settings',
            'update-reject-unit-transfer-parameters',
            'update-min-fusion-percent-parameters',
            'update-relance-parameters',
            'update-measure-preventive-parameters',
            'show-faq', 'store-faq', 'update-faq', 'delete-faq',
            'search-claim-any-reference',
            'attach-files-to-claim',
            'revive-staff',
            'list-notification-proof','pilot-list-notification-proof',
            'list-auth-config','update-auth-config',
            'activity-log',
            'list-any-notification-proof','pilot-list-any-notification-proof',
            'list-regulatory-reporting-claim-my-institution',
            'config-reporting-claim-any-institution',
            'list-system-usage-reporting', 'list-benchmarking-reporting', 'list-global-reporting',
            'list-regulatory-reporting-claim-any-institution','show-my-staff-monitoring',
            'config-reporting-claim-any-institution',
            'system-any-efficiency-report',
            'list-reporting-titles-configs','update-reporting-titles-configs','edit-reporting-titles-configs',
            'list-staff-revivals','list-unit-revivals',
            'logout-user-my-institution',
            'bci-monthly-reports', 'bci-annual-reports',
            'pilot-export-notification-proof', 'export-notification-proof',
            'configure-pilot-collector-discussion-attribute'

        ];

        $filialPermissions = [
            'list-claim-awaiting-assignment', 'show-claim-awaiting-assignment', 'merge-claim-awaiting-assignment',
            'store-claim-against-my-institution',
            'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
            'list-claim-awaiting-validation-my-institution', 'show-claim-awaiting-validation-my-institution', 'validate-treatment-my-institution',
            'list-my-claim-archived', 'show-my-claim-archived',
            'list-satisfaction-measured-my-claim', 'update-satisfaction-measured-my-claim',
            'list-client-from-my-institution', 'store-client-from-my-institution', 'update-client-from-my-institution', 'destroy-client-from-my-institution', 'show-client-from-my-institution',
            'show-dashboard-data-my-institution', 'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
            'list-my-discussions', 'store-discussion', 'destroy-discussion', 'list-discussion-contributors', 'add-discussion-contributor', 'remove-discussion-contributor', 'contribute-discussion',
            'update-my-institution-message-api',
            'list-monitoring-claim-my-institution',
            'update-my-institution',
            'list-my-unit', 'store-my-unit', 'update-my-unit', 'destroy-my-unit', 'show-my-unit',
            'update-processing-circuit-my-institution',
            'list-reporting-claim-my-institution',
            'list-staff-from-my-unit', 'store-staff-from-my-unit', 'update-staff-from-my-unit', 'destroy-staff-from-my-unit', 'show-staff-from-my-unit',
            'transfer-claim-to-circuit-unit',
            'list-claim-incomplete-against-my-institution', 'show-claim-incomplete-against-my-institution', 'update-claim-incomplete-against-my-institution',
            'list-user-my-institution', 'show-user-my-institution', 'store-user-my-institution',
            'history-list-create-claim',
            'history-list-treat-claim',
            'update-active-pilot',
            'unfounded-claim-awaiting-assignment',
            'search-claim-my-reference',
            'attach-files-to-claim',
            'revive-staff',
            "my-email-claim-configuration",
            'list-webhooks-config','store-webhooks-config','update-webhooks-config','delete-webhooks-config',
            'list-auth-config','update-auth-config',
            'list-notification-proof','pilot-list-notification-proof',
            'list-regulatory-reporting-claim-my-institution',
            'config-reporting-claim-my-institution',
            'list-system-usage-reporting', 'list-benchmarking-reporting', 'list-global-reporting',
            'system-my-efficiency-report','show-my-staff-monitoring',
            'list-reporting-titles-configs','update-reporting-titles-configs','edit-reporting-titles-configs',
            'list-staff-revivals','list-unit-revivals',
            'list-my-claim-unsatisfied',
            'list-closed-my-claims','close-my-claims',
            'list-escalation-config','update-escalation-config',
            'list-treatment-board','store-treatment-board','update-treatment-board','destroy-treatment-board',
            'logout-user-my-institution',
            'bci-monthly-reports', 'bci-annual-reports',
            'pilot-export-notification-proof', 'export-notification-proof','update-mail-parameters'

        ];

        $observatoryPermissions = [
            'list-category-client', 'store-category-client', 'update-category-client', 'destroy-category-client', 'show-category-client',
            'list-channel', 'store-channel', 'update-channel', 'destroy-channel', 'show-channel',
            'list-claim-awaiting-assignment', 'show-claim-awaiting-assignment', 'merge-claim-awaiting-assignment',
            'store-claim-without-client',
            'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
            'list-claim-awaiting-validation-any-institution', 'show-claim-awaiting-validation-any-institution', 'validate-treatment-any-institution',
            'list-claim-category', 'store-claim-category', 'update-claim-category', 'destroy-claim-category', 'show-claim-category',
            'list-claim-object', 'store-claim-object', 'update-claim-object', 'destroy-claim-object', 'show-claim-object',
            'update-claim-object-requirement',
            'list-any-claim-archived', 'show-any-claim-archived',
            'list-satisfaction-measured-any-claim', 'update-satisfaction-measured-any-claim',
            'show-mail-parameters', 'update-mail-parameters',
            'show-sms-parameters', 'update-sms-parameters',
            'list-currency', 'store-currency', 'update-currency', 'destroy-currency', 'show-currency',
            'show-dashboard-data-all-institution', 'show-dashboard-data-my-institution', 'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
            'list-my-discussions', 'store-discussion', 'destroy-discussion', 'list-discussion-contributors', 'add-discussion-contributor', 'remove-discussion-contributor', 'contribute-discussion',
            'list-faq-category', 'store-faq-category', 'update-faq-category', 'destroy-faq-category', 'show-faq-category',
            'list-message-apis', 'store-message-apis', 'update-message-apis', 'destroy-message-apis', 'update-my-institution-message-api',
            'list-monitoring-claim-any-institution',
            'list-position', 'store-position', 'update-position', 'destroy-position', 'show-position',
            'list-unit-type', 'store-unit-type', 'update-unit-type', 'destroy-unit-type', 'show-unit-type',
            'list-without-link-unit', 'store-without-link-unit', 'update-without-link-unit', 'destroy-without-link-unit', 'show-without-link-unit',
            'update-notifications',
            'list-performance-indicator', 'store-performance-indicator', 'update-performance-indicator', 'destroy-performance-indicator', 'show-performance-indicator', 'edit-performance-indicator',
            'update-processiong-circuit-without-institution',
            'list-relationship', 'store-relationship', 'update-relationship', 'destroy-relationship', 'show-relationship',
            'list-reporting-claim-any-institution',
            'list-severity-level', 'update-severity-level', 'show-severity-level',
            'list-staff-from-maybe-no-unit', 'store-staff-from-maybe-no-unit', 'update-staff-from-maybe-no-unit', 'destroy-staff-from-maybe-no-unit', 'show-staff-from-maybe-no-unit',
            'transfer-claim-to-unit',
            'list-claim-incomplete-without-client', 'show-claim-incomplete-without-client', 'update-claim-incomplete-without-client',
            'list-account-type', 'show-account-type', 'update-account-type', 'store-account-type', 'destroy-account-type',
            'list-user-any-institution', 'show-user-any-institution', 'store-user-any-institution',
            'list-delai-qualification-parameters', 'show-delai-qualification-parameters', 'store-delai-qualification-parameters', 'destroy-delai-qualification-parameters',
            'list-delai-treatment-parameters', 'show-delai-treatment-parameters', 'store-delai-treatment-parameters', 'destroy-delai-treatment-parameters',
            'update-components-parameters',
            'list-any-institution-type-role', 'show-any-institution-type-role', 'store-any-institution-type-role', 'update-any-institution-type-role', 'destroy-any-institution-type-role',
            'history-list-create-claim',
            'history-list-treat-claim',
            'update-active-pilot',
            'unfounded-claim-awaiting-assignment',
            'update-recurrence-alert-settings',
            'update-reject-unit-transfer-parameters',
            'update-min-fusion-percent-parameters',
            'update-relance-parameters',
            'update-measure-preventive-parameters',
            'show-faq', 'store-faq', 'update-faq', 'delete-faq',
            'search-claim-any-reference',
            'attach-files-to-claim',
            'revive-staff',
            'list-any-institution', 'store-any-institution', 'show-any-institution', 'update-any-institution', 'destroy-any-institution',
            "any-email-claim-configuration",
            'list-auth-config','update-auth-config',
            'activity-log',
            'list-notification-proof','pilot-list-notification-proof',
            'list-regulatory-reporting-claim-my-institution',
            'config-reporting-claim-any-institution',
            'list-system-usage-reporting', 'list-benchmarking-reporting', 'list-global-reporting',
            'list-regulatory-reporting-claim-any-institution',
            'config-reporting-claim-any-institution',
            'system-any-efficiency-report',
            'list-reporting-titles-configs','update-reporting-titles-configs','edit-reporting-titles-configs',
            'list-staff-revivals','list-unit-revivals',
            'show-my-staff-monitoring',
            'logout-user-my-institution',
            'configure-pilot-collector-discussion-attribute',
        ];

        $memberPermissions = [
            'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
            'show-dashboard-data-my-institution', 'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
            'list-my-discussions', 'store-discussion', 'destroy-discussion', 'list-discussion-contributors', 'add-discussion-contributor', 'remove-discussion-contributor', 'contribute-discussion',
            'history-list-treat-claim',
        ];

        $independantPermissions = [
            'list-any-institution', 'store-any-institution', 'update-any-institution', 'destroy-any-institution', 'show-any-institution',
            'list-category-client', 'store-category-client', 'update-category-client', 'destroy-category-client', 'show-category-client',
            'list-channel', 'store-channel', 'update-channel', 'destroy-channel', 'show-channel',
            'list-claim-awaiting-assignment', 'show-claim-awaiting-assignment', 'merge-claim-awaiting-assignment',
            'store-claim-against-my-institution',
            'list-claim-awaiting-treatment', 'show-claim-awaiting-treatment', 'rejected-claim-awaiting-treatment', 'self-assignment-claim-awaiting-treatment', 'assignment-claim-awaiting-treatment', 'list-claim-assignment-to-staff', 'show-claim-assignment-to-staff',
            'list-claim-awaiting-validation-my-institution', 'show-claim-awaiting-validation-my-institution', 'validate-treatment-my-institution',
            'list-claim-category', 'store-claim-category', 'update-claim-category', 'destroy-claim-category', 'show-claim-category',
            'list-claim-object', 'store-claim-object', 'update-claim-object', 'destroy-claim-object', 'show-claim-object',
            'update-claim-object-requirement',
            'list-my-claim-archived', 'show-my-claim-archived',
            'list-satisfaction-measured-my-claim', 'update-satisfaction-measured-my-claim',
            'list-client-from-my-institution', 'store-client-from-my-institution', 'update-client-from-my-institution', 'destroy-client-from-my-institution', 'show-client-from-my-institution',
            'show-mail-parameters', 'update-mail-parameters',
            'show-sms-parameters', 'update-sms-parameters',
            'list-currency', 'store-currency', 'update-currency', 'destroy-currency', 'show-currency',
            'show-dashboard-data-my-institution', 'show-dashboard-data-my-unit', 'show-dashboard-data-my-activity',
            'list-my-discussions', 'store-discussion', 'destroy-discussion', 'list-discussion-contributors', 'add-discussion-contributor', 'remove-discussion-contributor', 'contribute-discussion',
            'list-faq-category', 'store-faq-category', 'update-faq-category', 'destroy-faq-category', 'show-faq-category',
            'list-message-apis', 'store-message-apis', 'update-message-apis', 'destroy-message-apis', 'update-my-institution-message-api',
            'list-monitoring-claim-my-institution',
            'update-my-institution',
            'list-position', 'store-position', 'update-position', 'destroy-position', 'show-position',
            'list-unit-type', 'store-unit-type', 'update-unit-type', 'destroy-unit-type', 'show-unit-type',
            'list-my-unit', 'store-my-unit', 'update-my-unit', 'destroy-my-unit', 'show-my-unit',
            'list-category-client-from-my-institution', 'store-category-client-from-my-institution', 'update-category-client-from-my-institution', 'destroy-category-client-from-my-institution', 'show-category-client-from-my-institution',
            'update-notifications',
            'list-performance-indicator', 'store-performance-indicator', 'update-performance-indicator', 'destroy-performance-indicator', 'show-performance-indicator', 'edit-performance-indicator',
            'update-processing-circuit-my-institution',
            'list-reporting-claim-my-institution',
            'list-severity-level', 'update-severity-level', 'show-severity-level',
            'list-staff-from-my-unit', 'store-staff-from-my-unit', 'update-staff-from-my-unit', 'destroy-staff-from-my-unit', 'show-staff-from-my-unit',
            'transfer-claim-to-circuit-unit',
            'list-claim-incomplete-against-my-institution', 'show-claim-incomplete-against-my-institution', 'update-claim-incomplete-against-my-institution',
            'list-account-type', 'show-account-type', 'update-account-type', 'store-account-type', 'destroy-account-type',
            'list-user-my-institution', 'show-user-my-institution', 'store-user-my-institution',
            'list-delai-qualification-parameters', 'show-delai-qualification-parameters', 'store-delai-qualification-parameters', 'destroy-delai-qualification-parameters',
            'list-delai-treatment-parameters', 'show-delai-treatment-parameters', 'store-delai-treatment-parameters', 'destroy-delai-treatment-parameters',
            'update-components-parameters',
            'list-my-institution-type-role', 'show-my-institution-type-role', 'store-my-institution-type-role', 'update-my-institution-type-role', 'destroy-my-institution-type-role',
            'history-list-create-claim',
            'history-list-treat-claim',
            'update-active-pilot',
            'unfounded-claim-awaiting-assignment',
            'update-recurrence-alert-settings',
            'update-reject-unit-transfer-parameters',
            'update-min-fusion-percent-parameters',
            'update-relance-parameters',
            'update-measure-preventive-parameters',
            'show-faq', 'store-faq', 'update-faq', 'delete-faq',
            'search-claim-my-reference',
            'attach-files-to-claim',
            'revive-staff',
            "my-email-claim-configuration",
            'list-reporting-titles-configs', 'update-reporting-titles-configs', 'edit-reporting-titles-configs',
            'bci-monthly-reports', 'bci-annual-reports',
            'list-webhooks-config','store-webhooks-config','update-webhooks-config','delete-webhooks-config',
            'list-reporting-titles-configs','update-reporting-titles-configs','edit-reporting-titles-configs',
            'list-notification-proof','pilot-list-notification-proof',
            'list-staff-revivals','list-unit-revivals',
            'show-my-staff-monitoring',
            'list-my-claim-unsatisfied',
            'list-closed-my-claims','close-my-claims',
            'list-escalation-config','update-escalation-config',
            'list-treatment-board','store-treatment-board','update-treatment-board','destroy-treatment-board',
            'logout-user-my-institution',
            'bci-monthly-reports', 'bci-annual-reports',
            'pilot-export-notification-proof', 'export-notification-proof',
            'configuration-pilot','pilot-relance-other','reassignment_to_pilot',
            'list-claim-transferred-my-institution',
            'configure-pilot-collector-discussion-attribute',
            'system-my-efficiency-report',
            'internal-control-index','internal-control-claim-object-index','internal-control-store',
            'internal-control-claim','internal-control-claim-detail'
        ];

        $nature = Config::get('services.app_nature', 'PRO');

        foreach (Permission::where('guard_name', 'api')->get() as $permission) {

            if ($nature === 'MACRO') {
                if (in_array($permission->name, $holdingPermissions)) {
                    $this->addInstitutionTypeToPermission($permission, 'holding');
                }
                if (in_array($permission->name, $filialPermissions)) {
                    $this->addInstitutionTypeToPermission($permission, 'filiale');
                }
            }

            if ($nature === 'HUB') {
                if (in_array($permission->name, $observatoryPermissions)) {
                    $this->addInstitutionTypeToPermission($permission, 'observatory');
                }
                if (in_array($permission->name, $memberPermissions)) {
                    $this->addInstitutionTypeToPermission($permission, 'membre');
                }
            }

            if ($nature === 'PRO') {
                if (in_array($permission->name, $independantPermissions)) {
                    $this->addInstitutionTypeToPermission($permission, 'independant');
                }
            }

        }

    }
}
