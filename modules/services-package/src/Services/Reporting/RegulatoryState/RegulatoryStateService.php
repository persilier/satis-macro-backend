<?php

namespace Satis2020\ServicePackage\Services\Reporting\RegulatoryState;


use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Jobs\PdfRegulatoryStateReportingSendMail;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Traits\DataUserNature;
use Satis2020\ServicePackage\Traits\FilterClaims;
use Satis2020\ServicePackage\Traits\Metadata;
use Satis2020\ServicePackage\Traits\ReportingClaim;
use Satis2020\ServicePackage\Traits\UemoaReports;

class RegulatoryStateService
{
    use FilterClaims,DataUserNature,UemoaReports,ReportingClaim,Metadata;

    /**
     * @param $request
     * @return array
     * @throws \Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException
     */
    public function generateReportData($request)
    {
        $relations = [
            'claimObject.claimCategory',
        ];
        $receivedClaims = $this->getAllClaimsByPeriod($request,$relations)->get();
        $treatedClaims = $this->getClaimsByStatus($this->getAllClaimsByPeriod($request,$relations),Claim::CLAIM_VALIDATED,$relations)->get();
        $unresolvedClaims = $this->getAllClaimsByPeriod($request,$relations)
                ->whereNotIn("id",
                        $this->getClaimsByStatus($this->getAllClaimsByPeriod($request,$relations),
                        $request,Claim::CLAIM_VALIDATED)
                ->pluck("id")->toArray())->get();

        $libellePeriode = $this->libellePeriode(['startDate' => $this->periodeParams($request)['date_start'], 'endDate' =>$this->periodeParams($request)['date_end']]);

        return [
            'receivedClaims'=>$receivedClaims,
            'treatedClaims'=>$treatedClaims,
            'unresolvedClaims'=>$unresolvedClaims,
            'institution'=>$this->institution(),
            'title' => $this->getMetadataByName(Constants::REGULATORY_STATE_REPORTING)->title,
            'description' => $this->getMetadataByName(Constants::REGULATORY_STATE_REPORTING)->description,
            'logo' => $this->logo($this->institution()),
            'colorTableHeader' => $this->colorTableHeader(),
            'number_of_claims_litigated_in_court'=>$request->number_of_claims_litigated_in_court,
            'total_amount_of_claims_litigated_in_court'=>$request->total_amount_of_claims_litigated_in_court,
            'libellePeriode'=>$libellePeriode,
            'country'=>$this->institution()->country!=null?$this->institution()->country['name']:$this->institution()->name,
            'report_title'=>$this->getMetadataByName(Constants::REGULATORY_STATE_REPORTING)->title,
        ];
    }

    /**
     * @param $request
     * @return array
     */
    public function generateTaskReport($request,$institution)
    {
        $relations = [
            'claimObject.claimCategory',
        ];

        $receivedClaims = $this->getAllClaimsByPeriod($request,$relations)->get();
        $treatedClaims = $this->getClaimsByStatus($this->getAllClaimsByPeriod($request,$relations),$relations,Claim::CLAIM_VALIDATED)->get();
        $unresolvedClaims = $this->getAllClaimsByPeriod($request,$relations)
            ->whereNotIn("id",$this->getClaimsByStatus($this->getAllClaimsByPeriod($request,$relations),Claim::CLAIM_VALIDATED)
                ->pluck("id")->toArray())->get();

        $monthNumber = (int) date("n");
        if ($monthNumber<=6){
            $libellePeriode = __('messages.semester')." 1";
        }else{
            $libellePeriode = __('messages.semester')." 2";
        }

        return [
            'receivedClaims'=>$receivedClaims,
            'treatedClaims'=>$treatedClaims,
            'unresolvedClaims'=>$unresolvedClaims,
            'institution'=>$institution,
            'title' => $this->getMetadataByName(Constants::REGULATORY_STATE_REPORTING)->title,
            'description' => $this->getMetadataByName(Constants::REGULATORY_STATE_REPORTING)->description,
            'logo' => $this->logo($institution),
            'colorTableHeader' => $this->colorTableHeader(),
            'number_of_claims_litigated_in_court'=>"--",
            'total_amount_of_claims_litigated_in_court'=>"--",
            'libellePeriode'=>$libellePeriode,
            'country'=>$institution->name,
            'report_title'=>$this->getMetadataByName(Constants::REGULATORY_STATE_REPORTING)->title,
        ];
    }

    function generateAndSendReport($request,$institution,$reportingTask)
    {
        $data = $this->generateTaskReport($request,$institution);
        $logo = $this->logo($institution);
        $colorTableHeader = $this->colorTableHeader();
        $logoSatis = asset('assets/reporting/images/satisLogo.png');
        $view = view('ServicePackage::reporting.pdf-regulatory-state-reporting', compact("data","logo","logoSatis","colorTableHeader"))->render();


        $file = public_path().'/temp/'.Str::slug($data['title']).'-'.time().'.pdf';
        $pdf = App::make('dompdf.wrapper');
        $pdf->loadHTML($view);
        $pdf->save($file);

        $details = [
            'file' => $file,
            'email' => $this->emailDestinatairesReportingTasks($reportingTask),
            'reportingTask' => $reportingTask,
            'period' =>  $data['libellePeriode'],
        ];

        PdfRegulatoryStateReportingSendMail::dispatch($details);
    }
}