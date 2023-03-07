<?php


namespace Satis2020\ServicePackage\Consts;


use Satis2020\ServicePackage\Models\Claim;

class Constants
{

    const COUNTRIES_SERVICE_URL = "http://163.172.106.97:8020/api/";
    const BENIN_COUNTRY_ID = 24;
    const PAGINATION_SIZE = 10;

    const GLOBAL_STATE_REPORTING = 'global-state-reporting';
    const ANALYTICS_STATE_REPORTING = 'analytics-state-reporting';
    const OUT_OF_30_DAYS_REPORTING = 'out-of-30-days-reporting';
    const OUT_OF_TIME_CLAIMS_REPORTING = 'out-of-time-claims-reporting';
    const MONTHLY_REPORTING = 'monthly-reporting';
    const DAILY_REPORTING = 'daily-reporting';
    const WEEKLY_REPORTING = 'weekly-reporting';
    const BIANNUAL_REPORTING = 'biannual-reporting';
    const QUARTERLY_REPORTING = 'quarterly-reporting';
    const SYSTEM_USAGE_REPORTING = 'system-usage-report';
    const SYSTEM_EFFICIENCY_REPORTING = 'system-efficiency-reporting';
    const BENCHMARKING_REPORTING = 'benchmarking-report';
    const GLOBAL_REPORTING = 'global-report';
    const NOTIFICATION_PROOF = "notification-proof";
    const REGULATORY_STATE_REPORTING = 'regulatory-state-reporting';
    const ALL_STAFF = "allStaff";
    const ALL_PILOT = "allPilot";
    const ALL_UNIT = "allUnit";
    const ALL_COLLECTOR = "allCollector";




    const PROXY = "proxy";
    const PROXY_MODULES = "proxy_modules";
    const PROXY_HTTP_SERVER = "proxy_http_server";
    const PROXY_HTTPS_SERVER = "proxy_https_server";
    const PROXY_HTTP_PORT = "proxy_http_port";
    const PROXY_HTTPS_PORT = "proxy_https_port";


    static public function paginationSize()
    {
        return self::PAGINATION_SIZE;
    }

    static function getReportTypesNames()
    {
        $names = [];
        foreach (self::reportTypes() as $type) {
            array_push($names, $type['value']);
        }
        return $names;
    }

    static function reportTypes()
    {
        return [
            [
                'value' => self::GLOBAL_STATE_REPORTING, 'label' => 'Rapport global des réclamations'
            ],
            [
                'value' => self::ANALYTICS_STATE_REPORTING, 'label' => 'Rapport Analytique'
            ],
            [
                'value' => self::OUT_OF_30_DAYS_REPORTING, 'label' => 'Reclamation en retard de +30j'
            ],
            [
                'value' => self::OUT_OF_TIME_CLAIMS_REPORTING, 'label' => 'Réclamations en retard'
            ],
            [
                'value' => self::REGULATORY_STATE_REPORTING, 'label' => 'Rapports des états réglementaire'
            ],
            [
                'value' => self::MONTHLY_REPORTING, 'label' => 'Génération automatique par mois'
            ],
            [
                'value' => self::DAILY_REPORTING, 'label' => 'Génération automatique par jour'
            ],
            [
                'value' => self::WEEKLY_REPORTING, 'label' => 'Génération automatique par semaine'
            ],
            [
                'value' => self::BIANNUAL_REPORTING, 'label' => 'Génération automatique par Semestriel'
            ],
            [
                'value' => self::QUARTERLY_REPORTING, 'label' => 'Génération automatique par Trimestriel'
            ],
            [
                'value' => self::SYSTEM_USAGE_REPORTING, 'label' => 'Rapport utilisation système'
            ],
            [
                'value' => self::SYSTEM_EFFICIENCY_REPORTING, 'label' => "Rapport d'éfficacité du système"
            ],
            [
                'value' => self::BENCHMARKING_REPORTING, 'label' => 'Rapport de comparaison de l\'utilisation des fonctionnalités du système'
            ],
            [
                'value' => self::GLOBAL_REPORTING, 'label' => 'Rapport consolidé et rapport spécifique par institution'
            ],
            [
                'value' => self::NOTIFICATION_PROOF, 'label' => 'Preuve d\'Accusé de réception'
            ],


        ];
    }


    static function periodList()
    {

        return [
            [
                'value' => 'days', 'label' => 'Journalier'
            ],
            [
                'value' => 'weeks', 'label' => 'Hebdomadaire'
            ],
            [
                'value' => 'months', 'label' => 'Mensuel'
            ],
            [
                'value' => 'quarterly', 'label' => 'Trimestriel'
            ],
            [
                'value' => 'biannual', 'label' => 'Semestriel'
            ],
        ];
    }


    static function proxyModules()
    {
        return ['mail', 'sms', 'incoming_mail_service'];
    }

    static function getPeriodValues()
    {
        $names = [];
        foreach (self::periodList() as $type) {
            array_push($names, $type['value']);
        }
        return $names;
    }

    static function getSatisYearsFromCreation()
    {
        $years = [];
        $firstClaim = Claim::withTrashed()->orderBy('created_at','ASC')->first();
        if ($firstClaim!=null){
            $installationYear = (int)date("Y",strtotime($firstClaim->created_at));
        }else{
            $installationYear = (int)date('Y');
        }
        $currentYear = (int)date('Y');

        $diffInYear = $currentYear - $installationYear;
        if ($diffInYear==0){
            $years = [['label'=>date('Y'),"value"=>date('Y')]];
        }else{
            for ($i=0; $i<=$diffInYear;$i++){
                array_push($years,["label"=>$currentYear-$i,"value"=>$currentYear-$i]);
            }
        }

        return $years;
    }


    static function getClaimRelations()
    {
        return [
            'claimObject.claimCategory',
            'claimer',
            'relationship',
            'accountTargeted',
            'institutionTargeted',
            'unitTargeted',
            'requestChannel',
            'responseChannel',
            'amountCurrency',
            'createdBy.identite',
            'completedBy.identite',
            'files',
            'activeTreatment.satisfactionMeasuredBy.identite',
            'activeTreatment.responsibleStaff.identite',
            'activeTreatment.assignedToStaffBy.identite',
            'activeTreatment.responsibleUnit.parent',
            'revivals',
            'activeTreatment',
            'activeTreatment.validatedBy.identite',
            'activeTreatment.transferredToTargetInstitutionBy.identite',
            'activeTreatment.transferredToUnitBy.identite',
            'treatmentBoard.members.identite'
        ];

    }

    static function getTreatmentRelations()
    {

    }
}
