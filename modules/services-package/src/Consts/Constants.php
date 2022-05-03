<?php


namespace Satis2020\ServicePackage\Consts;


class Constants
{

    const COUNTRIES_SERVICE_URL = "http://163.172.106.97:8020/api/";
    const BENIN_COUNTRY_ID=24;
    const PAGINATION_SIZE = 10;

    const GLOBAL_STATE_REPORTING = 'global-state-reporting';
    const ANALYTICS_STATE_REPORTING = 'analytics-state-reporting';
    const OUT_OF_30_DAYS_REPORTING= 'out-of-30-days-reporting';
    const OUT_OF_TIME_CLAIMS_REPORTING= 'out-of-time-claims-reporting';
    const REGULATORY_STATE_REPORTING= 'regulatory-state-reporting';
    const MONTHLY_REPORTING= 'monthly-reporting';
    const DAILY_REPORTING= 'daily-reporting';
    const WEEKLY_REPORTING= 'weekly-reporting';
    const BIANNUAL_REPORTING= 'biannual-reporting';
    const QUARTERLY_REPORTING= 'quarterly-reporting';

    static public function  paginationSize()
    {
        return self::PAGINATION_SIZE;
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

        ];
    }

    static function periodList(){

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


    static function getReportTypesNames()
    {
        $names = [];
        foreach (self::reportTypes() as $type){
            array_push($names,$type['value']);
        }
        return $names;
    }

    static function getPeriodValues()
    {
        $names = [];
        foreach (self::periodList() as $type){
            array_push($names,$type['value']);
        }
        return $names;
    }

}
