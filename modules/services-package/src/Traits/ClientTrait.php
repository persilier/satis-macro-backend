<?php


namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Models\Client;
trait ClientTrait
{
    protected  function getAllClientByInstitution($institution){
        $clients = Client::with([
            'identite','type_client', 'category_client', 'accounts' => function($query) use ($institution){
                $query->where('institution_id', $institution);
            }
        ])->get();
        return $clients;
    }

    protected  function getOneClientByInstitution($institution, $id){
        $client = Client::with([
            'identite','type_client', 'category_client', 'accounts' => function($query) use ($institution){
                $query->where('institution_id', $institution);
            }
        ])->findOrFail($id);
        return $client;
    }


}