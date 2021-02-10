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
class StateAnalytiqueReportExcel implements FromCollection, WithCustomStartCell, WithHeadings
{
    private $claims;
    private $myInstitution;

    /**
     * GlobalStateReportExcel constructor.
     * @param $claims
     * @param $myInstitution
     * @param $colTelephone
     */
    public function __construct($claims, $myInstitution)
    {
        $this->claims = $claims;
        $this->myInstitution = $myInstitution;
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
            'claimCategorie' => 'Catégorie réclamation',
            'claimObject' => 'Object de réclamation',
            'totalClaim' => 'Nombres de réclamations',
            'totalTreated' => 'Nombre de réclamations traitées (traitées correspond à validées)',
            'totalUnfounded' => 'Nombre de réclamation non fondé',
            'totalNoValidated' => 'Nombre de réclamations en cours (les réclamations non encore validées)',
            'delayMediumQualification' => 'DELAI MOYEN DE QUALIFICATIONS(en jours ouvrés depuis l\'enregistrement)',
            'delayPlanned' => 'DELAI PREVU pour le traitement',
            'delayMediumTreatmentOpenDay' => 'DELAI MOYEN DE TRAITEMENT Ouvré( traitement = validation du traitement)',
            'delayMediumTreatmentWorkingDay' => 'DELAI MOYEN DE TRAITEMEN ouvrable',
            'percentageTreatedInDelay' => '% DE RECLAMATIONS TRAITEES DANS LE DELAI',
            'percentageTreatedOutDelay' => '% DE RECLAMATIONS traité hors délai',
            'percentageNoTreated' => '% DE RECLAMATIONS en cours de traitement',
        ];

        if(!$this->myInstitution){

            $header = Arr::prepend($header, 'Filiale', 'filiale');
        }

        return [
            $header,
        ];
    }


}
