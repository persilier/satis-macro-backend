<?php

namespace Satis2020\ServicePackage\Traits;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\TypeClient;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Unit;

/**
 * Trait IdentiteVerifiedTrait
 * @package Satis2020\ServicePackage\Traits
 */
trait IdentiteVerifiedTrait
{

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
                        "message" => __('messages.a_client_found',[],getAppLang()),
                        "client" => $filtered->first(),
                        "posts" => $posts
                    ]
                ];
        }
        return ['valide'=> true, 'message'=>''];
    }

}
