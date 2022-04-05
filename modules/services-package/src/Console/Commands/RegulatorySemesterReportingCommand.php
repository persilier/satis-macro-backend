<?php
namespace Satis2020\ServicePackage\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Satis2020\ServicePackage\Jobs\PdfReportingSendMail;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Traits\ReportingClaim;


/**
 * Class ReportingDaysCommand
 * @package Satis2020\ServicePackage\Console\Commands
 */
class RegulatorySemesterReportingCommand extends Command
{
    use ReportingClaim;

    protected $signature = 'service:generate-reporting-semester';

    protected $description = "Generate semester reporting";

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

        $dateStart = $dateNext->copy()->startOfDay();
        $dateEnd = $dateNext->copy()->endOfDay();


    }




}