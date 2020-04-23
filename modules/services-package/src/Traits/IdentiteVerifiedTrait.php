<?php

namespace Satis2020\ServicePackage\Traits;
use Satis2020\ServicePackage\Exceptions\SecureDeleteException;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\TypeClient;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Unit;
trait IdentiteVerifiedTrait
{
    protected function IdentiteExist($email, $telephone, $posts){
        $identites = Identite::All();
        if($identites->isNotEmpty()){
            $filtered = $identites->filter(function ($value, $key) use ($email, $telephone) {
                return (in_array($email ,$value->email) || in_array($telephone,$value->telephone));
            });
            if($filtered->first())
                return ['valide'=> false, 'message'=>
                    [
                        "message" => "Des informations liées à cette adresse email ou numéro de téléphone sont retrouvées dans la base. 
                                        Souhaitez vous enregistrer ce client avec ses informations existantes ?",
                        "identite" => $filtered->first(),
                        "posts" => $posts
                    ]
                ];
        }
        return ['valide'=> true, 'message'=>''];
    }


    public function IsValidClientIdentite($type_clients_id, $category_clients_id, $units_id, $institutions_id){
        if(!$type_client = TypeClient::whereId($type_clients_id)->whereInstitutions_id($institutions_id)->first())
            return ['valide'=>false, 'message'=>'Le type de client n\'existe pas dans l\'institution sélectionnée.'];

        if(!$category_client = CategoryClient::whereId($category_clients_id)->whereInstitutions_id($institutions_id)->first())
            return ['valide'=>false, 'message'=>'La catégorie de client n\'existe pas dans l\'institution sélectionnée.'];

        if(!$unit = Unit::whereId($units_id)->whereInstitution_id($institutions_id)->first())
            return ['valide'=>false, 'message'=>'Cette unité n\'existe pas dans l\'institution sélectionnée.'];

        return ['valide'=> true, 'message'=>''];
    }
}