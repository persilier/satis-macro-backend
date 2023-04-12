<?php

use Illuminate\Database\Seeder;
use Satis2020\ServicePackage\Database\Seeds\RegulatoryLimitMetadataSeeder;
use Satis2020\Escalation\Database\Seeds\EscalationConfigSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //$this->call(\Satis2020\ServicePackage\Database\Seeds\MetadataTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstitutionTypeTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\CategoryClientTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\TypeClientTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\IdentiteTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\ClientTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\PerformanceIndicatorTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstitutionTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\UnitTypeTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\UnitTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\AccountTypeTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\AccountTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\PositionTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\StaffTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\UnitLeadSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\SeverityLevelTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\ClaimCategoryTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\ClaimObjectTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\UsersTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\StaffFromAnyUnit\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\StaffFromMyUnit\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\SeverityLevel\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\AnyInstitution\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\StaffFromMaybeNoUnit\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\MyInstitution\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ClaimObject\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\ClaimCategory\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\Configuration\Database\Seeds\SmsTableSeeder::class);
//        $this->call(\Satis2020\Configuration\Database\Seeds\MailsTableSeeder::class);
//        $this->call(\Satis2020\PerformanceIndicatorPackage\Database\Seeds\RolesTableSeeder::class);
//      $this->call(\Satis2020\ClientFromMyInstitution\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\ClientFromAnyInstitution\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\ServicePackage\Database\Seeds\RequirementTableSeeder::class);
        //$this->call(\Satis2020\ClaimObjectRequirement\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\ServicePackage\Database\Seeds\RelationshipTableSeeder::class);
        //$this->call(\Satis2020\Relationship\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\Currency\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\Channel\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\Channel\Database\Seeds\ChannelsTableSeeder::class);
        //$this->call(\Satis2020\RegisterClaimAgainstAnyInstitution\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\UpdateClaimAgainstAnyInstitution\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\RegisterClaimAgainstMyInstitution\Database\Seeds\RolesTableSeeder::class);
        // $this->call(\Satis2020\RegisterClaimWithoutClient\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\UpdateClaimAgainstMyInstitution\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\UpdateClaimWithoutClient\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ProcessingCircuitMyInstitution\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ProcessingCircuitAnyInstitution\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ProcessingCircuitWithoutInstitution\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ClaimAwaitingAssignment\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\ClaimSeeder::class);
//        $this->call(\Satis2020\TransferClaimToTargetedInstitution\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\ProcessingCircuitSeeder::class);
//        $this->call(\Satis2020\TransferClaimToCircuitUnit\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\TransferClaimToUnit\Database\Seeds\RolesTableSeeder::class);
        // $this->call(\Satis2020\TransferClaimToTargetedInstitution\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\ServicePackage\Database\Seeds\StaffForUnitTreatmentClaimSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\CreateClaimForStaffSeeder::class);
//        $this->call(\Satis2020\ClaimAwaitingTreatment\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\StaffSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\HubStaffSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\ClaimAwaitingValidationSeeder::class);
//        $this->call(\Satis2020\ClaimAwaitingValidationMyInstitution\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ClaimAwaitingValidationAnyInstitution\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\MonitoringClaimAnyInstitution\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\MonitoringClaimMyInstitution\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\ServicePackage\Database\Seeds\CreateProcessRolesSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\ResetProcessSeeder::class);
//        $this->call(\Satis2020\ReportingClaimAnyInstitution\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ReportingClaimMyInstitution\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\Dashboard\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\Discussion\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\AssignClaimToStaffSeeder::class);
          //$this->call(\Satis2020\Notification\Database\Seeds\NotificationsTableSeeder::class);
//        $this->call(\Satis2020\Notification\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\MessageApi\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\Notification\Database\Seeds\RelanceNotificationsTableSeeder::class);
//          $this->call(\Satis2020\ServicePackage\Database\Seeds\TauxRelanceSendNotificationSeeder::class);
//          $this->call(\Satis2020\ServicePackage\Database\Seeds\TauxRelanceRoleSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\RolesInstitutionTypesSeeder::class);
//        $this->call(\Satis2020\FaqPackage\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\PurifyInstitutionsSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\PurifyClaimsSeeder::class);
//        $this->call(\Satis2020\Relance\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\CreateUserForTestInMacroSeeder::class);
//        $this->call(\Satis2020\AnyUser\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\MyUser\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\AccountType\Database\Seeds\RolesTableSeeder::class);
        // $this->call(\Satis2020\SatisfactionMeasuredAnyClaim\Database\Seeds\RolesTableSeeder::class);
        // $this->call(\Satis2020\SatisfactionMeasuredMyClaim\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\AnyClaimArchived\Database\Seeds\RolesTableSeeder::class);
        //$this->call(\Satis2020\MyClaimArchived\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\PresentationDataMACROSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\PresentationDataHUBSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\PresentationDataPROSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\AssignFeedbackPreferredChannelsToStaffSeeder::class);
        //$this->call(\Satis2020\Configuration\Database\Seeds\DelaiQualificationRoleSeeder::class);
        //$this->call(\Satis2020\Configuration\Database\Seeds\DelaiTreatmentRoleSeeder::class);
        //$this->call(\Satis2020\ServicePackage\Database\Seeds\MinFusionPercentSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\DelaiQualificationTreatmentSeeder::class);
//        $this->call(\Satis2020\ReportingClaimAnyInstitution\Database\Seeds\RolesConfigTableSeeder::class);
//        $this->call(\Satis2020\ReportingClaimMyInstitution\Database\Seeds\RolesConfigTableSeeder::class);
//        $this->call(\Satis2020\Faq\Database\Seeds\RolesTableFaqSeeder::class);
//        $this->call(\Satis2020\Configuration\Database\Seeds\ComponentSeeder::class);
//        $this->call(\Satis2020\ActivePilot\Database\Seeds\RolesTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\RecurrenceReclamationSeeder::class);
//        $this->call(\Satis2020\Notification\Database\Seeds\RecurrenceNotificationsTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\RejectUnitTransferLimitationSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\ModulePermissionsSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\InitializeChannelsTableSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\UpdateTimeLimitToClaimTableSeeder::class);
//        $this->call(\Satis2020\Notification\Database\Seeds\RevokeClaimNotificationsTableSeeder::class);
          $this->call(\Satis2020\ServicePackage\Database\Seeds\RoleDescriptionSeed::class);
//         $this->call(\Satis2020\ServicePackage\Database\Seeds\TruncateNotifJobs::class);

        /**************** Installation Seed **************************/
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallChannelSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallComponentSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallInstitutionTypeSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallInstitutionSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallMetadataSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallMetadataSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PurifyRolesPermissionsFilialSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PurifyRolesPermissionsHoldingSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PurifyRolesPermissionsMembreSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PurifyRolesPermissionsObservatorySeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PurifyRolesPermissionsIndependantSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\PermissionsInstitutionTypesSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\UpdatePermissionsDescriptionSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\ProxyConfigSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallRequirementSeeder::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\InstallAdministratorSeeder::class);

        /**************** Fin Installation Seed **************************/

//        $this->call(\Satis2020\Notification\Database\Seeds\ClaimHighForceFulnessNotification::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\ResetSeverityLevelsSeed::class);
//        $this->call(\Satis2020\ServicePackage\Database\Seeds\MeasurePreventiveRequireStatusSeeder::class);

//         $this->call(\Satis2020\ServicePackage\Database\Seeds\CreateOrUpdateComponentSeeder::class);
        //$this->call(\Satis2020\ServicePackage\Database\Seeds\NotificationsProofsSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\VerifyChannelsWhichCanBeTargetedSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\ReportingTitlesSeeder::class);
        $this->call(RegulatoryLimitMetadataSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\DelaiQualificationTreatmentSeeder::class);
        $this->call(\Satis2020\ServicePackage\Database\Seeds\ReportingTitlesSeeder::class);
        //$this->call(\Satis2020\ServicePackage\Database\Seeds\MetadataTableSeeder::class);
        //$this->call(RevokeTokensSeeder::class);
        //$this->call(RevokeTokensSeeder::class);
        $this->call(EscalationConfigSeeder::class);



    }

}
