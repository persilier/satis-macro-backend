<?php
namespace Satis2020\ServicePackage\Exports\UemoaReports;

use App\User;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Class GlobalStateReportExcel
 * @package Satis2020\ServicePackage\Exports\UemoaReports
 */
class StateAnalytiqueReportExcel implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $claims;
    private $myInstitution;
    private $libellePeriode;
    private $title;

    /**
     * GlobalStateReportExcel constructor.
     * @param $claims
     * @param $myInstitution
     * @param $libellePeriode
     * @param $reportName
     */
    public function __construct($claims, $myInstitution, $libellePeriode,$title)
    {
        $this->claims = $claims;
        $this->myInstitution = $myInstitution;
        $this->libellePeriode = $libellePeriode;
        $this->title = $title;
    }
    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->claims;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        $header = [
            'filiale' => 'Filiale',
            'claimCategorie' => 'Catégorie réclamation',
            'claimObject' => 'Object de réclamation',
            'totalClaim' => 'Nombres de réclamations',
            'totalTreated' => 'Nombre de réclamations traitées',
            'totalUnfounded' => 'Nombre de réclamation non fondé',
            'totalNoValidated' => 'Nombre de réclamations en cours',
            'delayMediumQualification' => 'Délai moyen de qualification (J) avec Weekend',
            'delayPlanned' => 'Délai prévu pour le traitement',
            'delayMediumTreatmentOpenDay' => 'Délai moyen de traitement (J) avec Weekend',
            'delayMediumTreatmentWorkingDay' => 'Délai moyen de traitement (J) sans Weekend',
            'percentageTreatedInDelay' => '% de réclamations traités dans le délai',
            'percentageTreatedOutDelay' => '% de réclamations traités hors délai',
            'percentageNoTreated' => '% de réclamations en cours de traitement',
            'countTreatedInDelay' => 'Nombre de réclamations traités dans le délai',
            'countTreatedOutDelay' => 'Nombre de réclamations traités hors délai',
            'countNoTreated' => 'Nombre de réclamations en cours de traitement',
        ];

        if($this->myInstitution){

            $header = Arr::except($header, 'filiale');
        }

        return [
            [
               $this->title
            ],
            [
                'Période : '.$this->libellePeriode
            ],
            $header,
        ];
    }

}
