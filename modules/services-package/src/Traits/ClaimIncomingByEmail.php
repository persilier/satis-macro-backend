<?php

namespace Satis2020\ServicePackage\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\EmailClaimConfiguration;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Illuminate\Support\Facades\Config;

trait ClaimIncomingByEmail
{

    protected function rulesIncomingEmail($id)
    {
        return [
            "email" => 'required|unique:email_claim_configurations,email,'.$id,
            "host" => 'required',
            "port" => 'required',
            "protocol" => 'required',
            "password" => 'required',
            "institution_id" => 'required|exists:institutions,id',
        ];
    }


    protected function editConfiguration($idInstitution)
    {
        return EmailClaimConfiguration::with('institution')->where('institution_id', $idInstitution)->first();
    }


    protected function subscriber($request, $routeName)
    {
        try {

            $params = Config::get('email-claim-configuration');

            $response = Http::post($params['api_subscriber'], [
                "app_name" => Institution::findOrFail($request->institution_id)->name,
                "url" => Config::get('email-claim-configuration.app_url_incoming_mail').route($routeName, $request->email, false),
                "mail_server" => $request->host,
                "mail_server_username" => $request->email,
                "mail_server_password" => $request->password,
                "mail_server_port" => $request->port,
                "mail_server_protocol" => $request->protocol,
                "app_login_url" => Config::get('email-claim-configuration.app_url_incoming_mail').route('passport.token', null, false),
                "app_login_params" => [
                    "grant_type" => $params['grant_type'],
                    "client_id" => $params['client_id'],
                    "client_secret" => $params['client_secret'],
                ]
            ])->json();

            if ($response['status'] !== 200) {
                return [
                    "error" => true,
                    "message" => $response['message']
                ];
            }

            return [
                "error" => false,
                "data" => $response['datas']
            ];

        } catch (\Exception $exception) {
            return [
                "error" => true,
                "message" => $exception->getMessage()
            ];
        }
    }

    protected function updateSubscriber($request, $emailClaimConfiguration, $routeName)
    {
        try {

            $params = Config::get('email-claim-configuration');

            $response = Http::put($params['api_subscriber'], [
                "url" => Config::get('email-claim-configuration.app_url_incoming_mail').route($routeName, $request->email, false),
                "mail_server" => $request->host,
                "mail_server_username" => $request->email,
                "mail_server_password" => $request->password,
                "mail_server_port" => $request->port,
                "mail_server_protocol" => $request->protocol,
                "app_login_url" => Config::get('email-claim-configuration.app_url_incoming_mail').route('passport.token', null, false),
                "app_id" => $emailClaimConfiguration->subscriber_id,
                "app_login_params" => [
                    "grant_type" => $params['grant_type'],
                    "client_id" => $params['client_id'],
                    "client_secret" => $params['client_secret'],
                ]
            ])->json();

            if (!$response['success']) {
                return [
                    "error" => true,
                    "message" => $response['message']
                ];
            }

            return [
                "error" => false,
                "data" => ""
            ];

        } catch (\Exception $exception) {
            return [
                "error" => true,
                "message" => $exception->getMessage()
            ];
        }
    }


    protected function storeConfiguration($request, $emailClaimConfiguration, $routeName)
    {
//        $testSmtp = $this->testSmtp($request->host, $request->port, $request->protocol, $request->email, $request->password);
//
//        if ($testSmtp['error']) {
//            return [
//                "error" => true,
//                "message" => $testSmtp['message']
//            ];
//        }

        $subscriber =  $emailClaimConfiguration ? $this->updateSubscriber($request, $emailClaimConfiguration, $routeName) : $this->subscriber($request, $routeName);

        if ($subscriber['error']) {

            try {
                Log::debug("subscribtion error",$subscriber);
            }catch (\Exception $exception){
                Log::info( $subscriber['message']);
            }

            return [
                "error" => true,
                "message" => "Les paramètres ne sont pas valides. L'adresse email saisie et/ou le nom (nom de l'intituion) de votre application est déjà utilisé par une autre institution.",
                "serviceErrors" => $subscriber['message']
            ];
        }

        $request->merge(['subscriber_id' => $emailClaimConfiguration ? $emailClaimConfiguration->subscriber_id : $subscriber['data']['app_id']]);

        return [
            "error" => false,
            "data" => EmailClaimConfiguration::updateOrCreate(['subscriber_id' => $request->subscriber_id], $request->all())
        ];
    }


    protected function readEmails($request, $typeText, $status, $configuration)
    {
        $registeredMail = [];

        foreach ($request->data as $email) {
            $error = false;
            try {

                $claim = $this->getDataIncomingEmail($email, $typeText);

                if (! $storeClaim = $this->storeClaim($claim, $status, $configuration)) {
                    $error = true;
                }

            }catch (\Exception $e){
                $error = true;
            }

            if (!$error) {
                array_push($registeredMail, $email['header']["message_id"][0]);
            }
        }

        return $registeredMail;
    }


    protected function getDataIncomingEmail($email, $typeText)
    {
        return [
            "name" => $email['header']['from']['name'],
            "address" => $email['header']['from']['address'],
            "date" => $email['header']['date'],
             $name_array = explode(" ", $email['header']['from']['name'], 2),
            "firstname" => $name_array[0],
            "lastname" => sizeof($name_array) > 1 ? $name_array[1] : $name_array[0],
            "description" => $typeText === "html_text" ? $email['htmlMessage'] : $email['plainMessage'],
            "attachments" => $email["attachments"]
        ];
    }


    protected function storeClaim($claim, $status, $configuration)
    {
        try {

            if (! $identity = $this->identityVerified($claim)) {
                $identity = Identite::create([
                    "firstname" => $claim['firstname'],
                    "lastname" => $claim['lastname'],
                    "email" => [$claim['address']],
                ]);
            }

            $claimStore = Claim::create([
                'reference' => $this->createReference($configuration->institution_id),
                'description' => $claim['description'],
                'status' => $status,
                'claimer_id' => $identity->id,
                "institution_targeted_id" => $configuration->institution_id,
                "request_channel_slug" => "email",
                "response_channel_slug" => "email"
            ]);

            for ($i = 0; $i < sizeof($claim['attachments']); $i++) {
                $save_img = $this->base64SaveImg($claim['attachments'][$i], 'claim-attachments/', $i);
                $claimStore->files()->create(['title' => "Incoming mail attachment ".$claimStore->reference, 'url' => $save_img['link']]);
            }

            return true;

        } catch (\Exception $exception) {
            return false;
        }
    }


    protected function identityVerified($claim)
    {
        $identity = null;

        $verifyEmail = $this->handleInArrayUnicityVerification([$claim['address']], 'identites', 'email');

        if (!$verifyEmail['status']) {
            $identity = $verifyEmail['entity'];
        }

        return $identity;
    }

}