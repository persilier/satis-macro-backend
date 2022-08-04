<?php


namespace Satis2020\ServicePackage\Consts;


use Illuminate\Support\Arr;
use Satis2020\ServicePackage\Models\Claim;

class Constants
{

    const COUNTRIES_SERVICE_URL = "http://163.172.106.97:8020/api/";
    const BENIN_COUNTRY_ID=24;
    const PAGINATION_SIZE = 10;

    const GLOBAL_STATE_REPORTING = 'global-state-reporting';
    const ANALYTICS_STATE_REPORTING = 'analytics-state-reporting';
    const OUT_OF_30_DAYS_REPORTING= 'out-of-30-days-reporting';
    const OUT_OF_TIME_CLAIMS_REPORTING= 'out-of-time-claims-reporting';
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

    static function getReportTypesNames()
    {
        $names = [];
        foreach (self::reportTypes() as $type){
            array_push($names,$type['value']);
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
            $years = [date('Y')];
        }else{
            for ($i=0; $i<=$diffInYear;$i++){
                array_push($years,$currentYear-$i);
            }
        }

        return $years;
    }
}
