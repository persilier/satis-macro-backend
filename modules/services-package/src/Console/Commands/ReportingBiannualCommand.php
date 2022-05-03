<?php
namespace Satis2020\ServicePackage\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\ReportingTask;
use Satis2020\ServicePackage\Services\Reporting\RegulatoryState\RegulatoryStateService;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\Metadata;
use Satis2020\ServicePackage\Traits\ReportingClaim;


/**
 * Class ReportingBiannualCommand
 * @package Satis2020\ServicePackage\Console\Commands
 */
class ReportingBiannualCommand extends Command
{
    use ReportingClaim,DataUserNature,Metadata;

    protected $signature = 'service:generate-reporting-biannual';

    protected $description = 'Génération automatique par mois des rapporting et l\'envoie par email.';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @param RegulatoryStateService $service
     * @throws \Throwable
     */
    public function handle(Request $request,RegulatoryStateService $service)
    {
        $date = now();

        $dateNext = $date->copy()->subDay();

        $dateStart = $dateNext->copy()->subQuarters(2)->startOfQuarter();

        $dateEnd = $dateStart->copy()->addQuarters(2)->endOfQuarter();

        $request->merge(['date_start' => $dateStart, 'date_end' => $dateEnd]);

        $reportinTasks = $this->getAllReportingTasks('biannual', $date);

        if($reportinTasks->isNotEmpty()){

            foreach ($reportinTasks as $reportingTask){

                switch ($reportingTask->reporting_type){
                    /*case ReportingTask::UEAMOA_REPORT :
                        break;
                    case ReportingTask::SATIS_REPORT:
                        break;*/
                    case Constants::REGULATORY_STATE_REPORTING:
                        $institutions = Institution::query()->get();
                        foreach ($institutions as $institution){
                            $request->merge(['institution_id'=>$institution->id]);
                            $service->generateAndSendReport($request,$institution,$reportingTask);
                        }
                        break;
                    default:
                        $this->TreatmentReportingTasks($request, $reportingTask);
                        break;
                }


            }

        }

    }

}