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
    private $relationShip;

    /**
     * GlobalStateReportExcel constructor.
     * @param $claims
     * @param $myInstitution
     * @param $libellePeriode
     * @param $reportName
     * @param $relationShip
     */
    public function __construct($claims, $myInstitution, $libellePeriode,$reportName, $relationShip)
    {
        $this->claims = $claims;
        $this->myInstitution = $myInstitution;
        $this->libellePeriode = $libellePeriode;
        $this->reportName = $reportName;
        $this->relationShip = $relationShip;
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
            'relationShip' => 'Relation avec le réclamant',
            'typeClient' => 'Type Client',
            'client' => 'Client',
            'reference' => 'Référence',
            'account' => 'N° compte',
            'telephone' => 'Téléphone',
            'agence' => 'Agence',
            'claimCategorie' => 'Catégorie réclamation',
            'claimObject' => 'Objet réclamation',
            'requestChannel' => 'Canal de réception',
            'commentClient' => 'Commentaire (client)',
            'functionTreating' => 'Unité de traitement',
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
            'accountCurrency' => 'Devise du montant',
            'collector_info' => 'Collecteur',
            'unit_info' => 'Unité concernée',
            'pilot_in_charge_info' => 'Pilote en charge',
        ];

        if($this->myInstitution){

            $header = Arr::except($header, 'filiale');
        }

        if($this->relationShip){

            $header = Arr::except($header, 'typeClient');
            $header = Arr::except($header, 'client');
            $header = Arr::except($header, 'account');

        }else{

            $header = Arr::except($header, 'relationShip');
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
