<?php
namespace Satis2020\ServicePackage\Exports\UemoaReports;

use App\User;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Class GlobalStateReportExcel
 * @package Satis2020\ServicePackage\Exports\UemoaReports
 */
class StateReportExcel implements FromCollection, WithCustomStartCell, WithHeadings
{
    private $claims;
    private $myInstitution;
    private $colTelephone;

    /**
     * GlobalStateReportExcel constructor.
     * @param $claims
     * @param $myInstitution
     * @param $colTelephone
     */
    public function __construct($claims, $myInstitution, $colTelephone)
    {
        $this->claims = $claims;
        $this->myInstitution = $myInstitution;
        $this->colTelephone = $colTelephone;
    }
    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->claims;
    }


    /**
     * @return string
     */
    public function startCell(): string
    {
        return 'A2';
    }


    /**
     * @return array
     */
    public function headings(): array
    {
        $header = [
            'typeClient' => 'Type Client',
            'client' => 'Client',
            'account' => 'N° compte',
            'telephone' => 'Téléphone',
            'agence' => 'Agence (agence concernée sinon agence du client)',
            'claimCategorie' => 'Catégorie réclamation',
            'claimObject' => 'Objet réclamation',
            'requestChannel' => 'Canal de réception',
            'commentClient' => 'Commentaire (client) - desciption de la réclamation',
            'staffTreating' => 'Fonction de traitement - staff traitant',
            'solution' => 'Commentaire (fonction de traitement) solution apportée par le staff',
            'status' => 'Statut',
            'dateRegister' => 'Date réclamation',
            'dateQualification' => 'Date qualification',
            'dateTreatment' => 'Date traitement (= date validation)',
            'dateClosing' => 'Date clôture',
            'delayQualificationOpenDay' => 'Délai de qualification (J) jour(s) ouvré(s)',
            'delayQualificationWorkingDay' => 'Délai de qualification (J) en jour(s) ouvrable(s)',
            'delayTreatmentOpenDay' =>  'Délai de traitement en jour(s) ouvré(s)',
            'delayTreatmentWorkingDay' => 'Délai de traitement en jour(s) ouvrable(s)',
            'amountDisputed' => 'Montant réclamé' ,
            'accountCurrency' => 'Devise du montant'
        ];

        if($this->colTelephone){

            $header = Arr::except($header, 'telephone');
        }

        if(!$this->myInstitution){

            $header = Arr::prepend($header, 'Filiale', 'filiale');
        }

        return [
            $header,
        ];
    }


}
