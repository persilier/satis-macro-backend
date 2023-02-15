<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Rules\ChannelIsForResponseRules;

/**
 * Trait UnitsPrediction
 * @package Satis2020\ServicePackage\Traits
 */
trait ScanFileClaimPrediction
{

    protected function informationExtraction($file)
    {
        $data = [
            "user_question"=> "quel est le nom du client ?,quel est le numéro de téléphone du client ?, quel est le prénom du client ?, quel est l'email du client ?, quel est le sexe du client ?, quel est la ville du client ?",
            "claim_question"=>"quel est le numéro du compte concerné ?,quel est la date de l'évènement?, quel est le montant réclamé ?, quelle est la description de la réclamation?, quelle est l'attente de la réclamation?",
            "type"=>$file->getClientOriginalExtension(),
            "threshold"=>0.2
        ];

        $result = Http::attach('file', file_get_contents($file), $file->getClientOriginalName())
            ->post(Config::get("email-claim-configuration.scan_file_claim_prediction"), $data);
        return json_decode($result->body(), true);
    }


    protected function rulesInformationExtraction()
    {
        $data = [
            'file' => 'required|max:20000|mimes:doc,pdf,docx,txt,jpeg,bmp,png,xls,xlsx,csv',
        ];

        return $data;
    }

}
