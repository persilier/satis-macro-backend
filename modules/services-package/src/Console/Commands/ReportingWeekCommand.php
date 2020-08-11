<?php
namespace Satis2020\ServicePackage\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Traits\ReportingClaim;


/**
 * Class ReportingDaysCommand
 * @package Satis2020\ServicePackage\Console\Commands
 */
class ReportingWeekCommand extends Command
{
    use ReportingClaim;

    protected $signature = 'service:generate-reporting-week';

    protected $description = 'Génération automatique par semaine des rapporting et l\'envoie par email.';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Request $request
     * @throws \Throwable
     */
    public function handle(Request $request)
    {
        $date = now();

        $dateNext = $date->copy()->subDay();

        $dateStart = $dateNext->copy()->startOfWeek();
        $dateEnd = $dateNext->copy()->endOfWeek();

        $request->merge(['date_start' => $dateStart, 'date_end' => $dateEnd]);

        $reportinTasks = $this->getAllReportingTasks('weeks', $date);

        if($reportinTasks->isNotEmpty()){

            foreach ($reportinTasks as $reportinTask){

                $this->TreatmentReportingTasks($request, $reportinTask);

            }

        }

    }

}