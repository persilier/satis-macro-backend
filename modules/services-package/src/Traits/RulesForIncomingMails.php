<?php

namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\EmailClaimConfiguration;


trait RulesForIncomingMails
{

    protected function rulesOffice365($id)
    {
        return [
            "email" => 'required|unique:email_claim_configurations,email,' . $id,
            "host" => 'required',
            "password" => 'required',
            "institution_id" => 'required|exists:institutions,id',
            "app_tenant" => 'required',
            "app_client_secret"=> "required",
            "app_client_id"=> "required",
            "host" => 'required',
            "type" => 'required',
        ];
    }

    protected function rulesOthers($id)
    {
        return [
            "email" => 'required|unique:email_claim_configurations,email,' . $id,
            "host" => 'required',
            "password" => 'required',
            "institution_id" => 'required|exists:institutions,id',
            "port" => 'required',
            "protocol" => 'required',
            "type" => 'required',
        ];

    }

    protected function transformQueryForOthers($request, $emailClaimConfiguration, $routeName, $params)
    {

        $requestData = [
            "app_name" => Str::random(16) . '-' . Institution::findOrFail($request->institution_id)->name,
            "url" => Config::get('email-claim-configuration.app_url_incoming_mail') . route($routeName, $request->email, false),
            "mail_server" => $request->host,
            "mail_server_username" => $request->email,
            "mail_server_password" => $request->password,
            "mail_server_port" => $request->port,
            "mail_server_protocol" => $request->protocol,
            "app_login_url" => Config::get('email-claim-configuration.app_url_incoming_mail') . route('passport.token', null, false),
            "app_id" => $emailClaimConfiguration->subscriber_id,
            "type" => $request->type,
            "app_login_params" => [
                "grant_type" => $params['grant_type'],
                "client_id" => $params['client_id'],
                "client_secret" => $params['client_secret'],
            ]
        ];

        return  $requestData;

    }

    protected function transformQueryForOffice365($request, $emailClaimConfiguration, $routeName, $params)
    {
          
        $requestData = [
            "app_name" => Str::random(16) . '-' . Institution::findOrFail($request->institution_id)->name,
            "url" => Config::get('email-claim-configuration.app_url_incoming_mail') . route($routeName, $request->email, false),
            "app_login_url" => Config::get('email-claim-configuration.app_url_incoming_mail') . route('passport.token', null, false),
            "app_id" => $emailClaimConfiguration->subscriber_id,
            "app_tenant" => $request->app_tenant,
            "app_client_secret" => $request->app_client_secret,
            "app_client_id" => $request->app_client_id,
            "app_username" => $request->email,
            "app_password" => $request->password,
            "type" => $request->type,
            "app_login_params" => [
                "grant_type" => $params['grant_type'],
                "client_id" => $params['client_id'],
                "client_secret" => $params['client_secret'],
            ]
           
        ];

        return  $requestData;

    }

    protected function NewTransformQueryForOthers($request, $routeName, $params)
    {

        
        $requestData = [

            "app_name" => Str::random(16) . '-' . Institution::findOrFail($request->institution_id)->name,
            "url" => Config::get('email-claim-configuration.app_url_incoming_mail') . route($routeName, $request->email, false),
            "mail_server" => $request->host,
            "mail_server_username" => $request->email,
            "mail_server_password" => $request->password,
            "mail_server_port" => $request->port,
            "mail_server_protocol" => $request->protocol,
            "app_login_url" => Config::get('email-claim-configuration.app_url_incoming_mail') . route('passport.token', null, false),
            "type" => $request->type,
            "app_login_params" => [
                "grant_type" => $params['grant_type'],
                "client_id" => $params['client_id'],
                "client_secret" => $params['client_secret'],
            ]
        ];

        
        return  $requestData;

    }

    protected function NewTransformQueryForOffice365($request, $routeName, $params)
    {
          
        $requestData = [
            
            "app_name" => Str::random(16) . '-' . Institution::findOrFail($request->institution_id)->name,
            "url" => Config::get('email-claim-configuration.app_url_incoming_mail') . route($routeName, $request->email, false),
            "app_login_url" => Config::get('email-claim-configuration.app_url_incoming_mail') . route('passport.token', null, false),
            "app_tenant" => $request->app_tenant,
            "app_client_secret" => $request->app_client_secret,
            "app_client_id" => $request->app_client_id,
            "app_username" => $request->email,
            "app_password" => $request->password,
            "type" => $request->type,
            "app_login_params" => [
                "grant_type" => $params['grant_type'],
                "client_id" => $params['client_id'],
                "client_secret" => $params['client_secret'],
            ]
           
        ];

        return  $requestData;

    }

    protected function createOrUpdateConfigurationEmail($request)
    {
        $data = [];

        if ($request->type == "office365") {
           $data += [
            "email" => $request->email,
            "host" => "https://graph.microsoft.com/.default",
            "port" => "993",
            "protocol" => $request->type,
            "type" => $request->type,
            "password" => $request->password,
            "institution_id" => $request->institution_id,
            "app_tenant" => $request->app_tenant,
            "app_client_secret" => $request->app_client_secret,
            "app_client_id" => $request->app_client_id,

           ];
        }
        
        if ($request->type == "others") {
            $data = $request->all();
        }
        
        return EmailClaimConfiguration::updateOrCreate(['subscriber_id' => $request->subscriber_id], $data);

    
    }
}