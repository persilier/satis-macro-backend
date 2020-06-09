<?php


namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Models\ClientInstitution;
trait ClientTrait
{
    protected  function getOneClientByInstitution($institution, $id){
        $client = ClientInstitution::with(
            'client.identite',
            'category_client',
            'institution',
            'accounts.accountType'
        )->where('institution_id',$institution)->where('client_id',$id)->firstOrFail();
        return $client;
    }

    protected  function getAllClientByInstitution($institution){
        $clients = ClientInstitution::with(
            'client.identite',
            'category_client',
            'institution',
            'accounts.accountType'
        )->where('institution_id',$institution)->get();
        return $clients;
    }

    protected  function getOneAccountClientByInstitution($institution, $account){

        $client = ClientInstitution::with([
            'client.identite',
            'category_client',
            'institution',
            'accounts.accountType'
        ])->where(function ($query) use ($account){
            $query->whereHas('accounts', function ($q) use ($account){
                $q->where('id', $account);
            });
        })->where('institution_id',$institution)->firstOrFail();

        return $client;
    }


    protected  function getOneAccountClient($account){

        $client = ClientInstitution::with([
            'client.identite',
            'category_client',
            'institution',
            'accounts.accountType'
        ])->where(function ($query) use ($account){
            $query->whereHas('accounts', function ($q) use ($account){
                $q->where('id', $account);
            });
        })->firstOrFail();

        return $client;
    }




}