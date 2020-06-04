<?php


namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Models\Client;
trait ClientTrait
{
    protected  function getAllClientByInstitution($institution){
        $clients = Client::with([
            'identite','type_client', 'category_client', 'accounts' => function($query) use ($institution){
                $query->where('institution_id', $institution)->get()->load('accountType');
            }
        ])->get();
        return $clients;
    }

    protected  function getOneAccountClientByInstitution($institution, $id){
        $client = Client::with([
            'identite','type_client', 'category_client', 'accounts' => function($query) use ($institution, $id){
                $query->where('institution_id', $institution)->where('id',$id)->get()->load('accountType');
            }
        ])->firstOrFail();
        return $client;
    }

    protected  function getOneClientByInstitution($institution, $id){
        $client = Client::with([
            'identite','type_client', 'category_client', 'accounts' => function($query) use ($institution){
                $query->where('institution_id', $institution)->get()->load('accountType');
            }
        ])->findOrFail($id);
        return $client;
    }


}