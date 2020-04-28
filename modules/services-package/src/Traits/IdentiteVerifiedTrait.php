<?php

namespace Satis2020\ServicePackage\Traits;
use Satis2020\ServicePackage\Exceptions\SecureDeleteException;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\TypeClient;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Unit;
trait IdentiteVerifiedTrait
{

    public function IsValidClientIdentite($type_clients_id, $category_clients_id, $units_id, $institutions_id){
        if(!$type_client = TypeClient::whereId($type_clients_id)->whereInstitutions_id($institutions_id)->first())
            return ['valide'=>false, 'message'=>'Le type de client n\'existe pas dans l\'institution sélectionnée.'];

        if(!$category_client = CategoryClient::whereId($category_clients_id)->whereInstitutions_id($institutions_id)->first())
            return ['valide'=>false, 'message'=>'La catégorie de client n\'existe pas dans l\'institution sélectionnée.'];

        if(!$unit = Unit::whereId($units_id)->whereInstitution_id($institutions_id)->first())
            return ['valide'=>false, 'message'=>'Cette unité n\'existe pas dans l\'institution sélectionnée.'];

        return ['valide'=> true, 'message'=>''];
    }


    public function IsValidClient($account_number, $institutions_id, $identites_id, $posts){
        $clients = Client::All();
        if($clients->isNotEmpty()){
            $filtered = $clients->filter(function ($value, $key) use ($account_number, $institutions_id, $identites_id) {
                return (in_array($account_number ,$value->account_number) && ($institutions_id == $value->institutions_id)
                            && ($identites_id == $value->$identites_id));
            });
            if($filtered->first())
                return ['valide'=> false, 'message'=>
                    [
                        "message" => "L'un des clients est retrouvé dans l\'institution sélectionnée avec ce numéro de compte. 
                                        Souhaitez vous apporter une modification à ce compte ?",
                        "client" => $filtered->first(),
                        "posts" => $posts
                    ]
                ];
        }
        return ['valide'=> true, 'message'=>''];
    }

}