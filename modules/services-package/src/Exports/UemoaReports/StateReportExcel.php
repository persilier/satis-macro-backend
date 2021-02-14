<?php
namespace Satis2020\ServicePackage\Exports\UemoaReports;

use App\User;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Class GlobalStateReportExcel
 * @package Satis2020\ServicePackage\Exports\UemoaReports
 */
class StateReportExcel implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $claims;
    private $myInstitution;
    private $libellePeriode;
    private $reportName;

    /**
     * GlobalStateReportExcel constructor.
     * @param $claims
     * @param $myInstitution
     * @param $colTelephone
     * @param $libellePeriode
     * @param $reportName
     */
    public function __construct($claims, $myInstitution, $libellePeriode,$reportName)
    {
        $this->claims = $claims;
        $this->myInstitution = $myInstitution;
        $this->libellePeriode = $libellePeriode;
        $this->reportName = $reportName;
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
            'typeClient' => 'Type Client',
            'client' => 'Client',
            'account' => 'N° compte',
            'telephone' => 'Téléphone',
            'agence' => 'Agence',
            'claimCategorie' => 'Catégorie réclamation',
            'claimObject' => 'Objet réclamation',
            'requestChannel' => 'Canal de réception',
            'commentClient' => 'Commentaire (client)',
            'functionTreating' => 'Fonction de traitement',
            'staffTreating' => 'Staff traitant',
            'solution' => 'Solution apportée par le staff',
            'status' => 'Statut',
            'dateRegister' => 'Date réclamation',
            'dateQualification' => 'Date qualification',
            'dateTreatment' => 'Date traitement',
            'dateClosing' => 'Date clôture',
            'delayQualifWithWeekend' => 'Délai de qualification (J) avec Weekend',
            'delayTreatWithWeekend' =>  'Délai de traitement (J) avec Weekend',
            'delayTreatWithoutWeekend' => 'Délai de traitement (J) sans Weekend',
            'amountDisputed' => 'Montant réclamé' ,
            'accountCurrency' => 'Devise du montant'
        ];

//        if($this->colTelephone){
//
//            $header = Arr::except($header, 'telephone');
//        }

        if($this->myInstitution){

            $header = Arr::except($header, 'filiale');
        }

        return [
            [
                $this->reportName
            ],
            [
                'Période : '.$this->libellePeriode
            ],
            $header,
        ];
    }


}
