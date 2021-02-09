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
class GlobalStateReportExcel implements FromCollection, WithCustomStartCell, WithHeadings
{
    private $claims;
    private $myInstitution;

    /**
     * GlobalStateReportExcel constructor.
     * @param $claims
     * @param $myInstitution
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
            'Type Client',
            'Client',
            'N° compte',
            'Agence (agence concernée sinon agence du client)',
            'Catégorie réclamation',
            'Objet réclamation',
            'Canal de réception',
            'Commentaire (client) - desciption de la réclamation',
            'Fonction de traitement - staff traitant',
            'Commentaire (fonction de traitement) solution apportée par le staff',
            'Statut',
            'Date réclamation',
            'Date qualification',
            'Date traitement (= date validation)',
            'Date clôture',
            'Délai de qualification (J) en jours ouvrables',
            'Délai de traitement (J) en jours ouvrés et ouvrables',
            'Montant réclamé',
            'Devise du montant'
        ];

        if(!$this->myInstitution){

            $header = Arr::prepend($header, 'Filiale', 'filiale');
        }

        return [
            $header,
        ];
    }


}
