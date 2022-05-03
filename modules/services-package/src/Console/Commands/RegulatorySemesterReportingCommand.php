<?php
namespace Satis2020\ServicePackage\Console\Commands;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Jobs\PdfReportingSendMail;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\ReportingTask;
use Satis2020\ServicePackage\Services\Reporting\RegulatoryState\RegulatoryStateService;
use Satis2020\ServicePackage\Traits\ReportingClaim;


/**
 * Class ReportingDaysCommand
 * @package Satis2020\ServicePackage\Console\Commands
 */
class RegulatorySemesterReportingCommand extends Command
{
    use ReportingClaim;

    protected $signature = 'service:generate-semester-reporting';

    protected $description = "Generate semester reporting";

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

        $reportinTasks = $this->getAllReportingTasks("biannual", $date);

        if($reportinTasks->isNotEmpty()){

            foreach ($reportinTasks as $reportingTask){
                $institutions = Institution::query()->limit(1)->get();
                $reportingTask = ReportingTask::first();
                foreach ($institutions as $institution){
                    $request->merge(['institution_id'=>$institution->id]);
                    $service->generateAndSendReport($request,$institution,$reportingTask);
                }
            }

        }else{
            $this->info("Oops! No task found");
        }
    }




}