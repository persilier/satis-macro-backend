<?php

namespace Satis2020\ServicePackage\Traits;


use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\EmailClaimConfiguration;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Institution;
use Illuminate\Support\Facades\Config;
use Satis2020\ServicePackage\Notifications\AcknowledgmentOfReceipt;
use Satis2020\ServicePackage\Notifications\RegisterAClaim;

trait ClaimIncomingByEmail
{

    protected function rulesIncomingEmail($id)
    {
        return [
            "email" => 'required|unique:email_claim_configurations,email,' . $id,
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

            $httpClient = Http::withHeaders([]);

            $proxyConfigs = Config::get('proxy');

            if ($proxyConfigs['http'] || $proxyConfigs['https']) {
                $httpClient = $httpClient->withOptions([
                    'proxy' => $proxyConfigs
                ]);
            }

            $params = Config::get('email-claim-configuration');

            $requestData = [
                "app_name" => Str::random(16) . '-' . Institution::findOrFail($request->institution_id)->name,
                "url" => Config::get('email-claim-configuration.app_url_incoming_mail') . route($routeName, $request->email, false),
                "mail_server" => $request->host,
                "mail_server_username" => $request->email,
                "mail_server_password" => $request->password,
                "mail_server_port" => $request->port,
                "mail_server_protocol" => $request->protocol,
                "app_login_url" => Config::get('email-claim-configuration.app_url_incoming_mail') . route('passport.token', null, false),
                "app_login_params" => [
                    "grant_type" => $params['grant_type'],
                    "client_id" => $params['client_id'],
                    "client_secret" => $params['client_secret'],
                ]
            ];

            $response = $httpClient->post($params['api_subscriber'], $requestData)->json();

            if ($response == null) {
                $response = Http::post($params['api_subscriber'], $requestData)->json();
            }

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

            $httpClient = Http::withHeaders([]);

            $params = Config::get('email-claim-configuration');

            $requestData = [
                "url" => Config::get('email-claim-configuration.app_url_incoming_mail') . route($routeName, $request->email, false),
                "mail_server" => $request->host,
                "mail_server_username" => $request->email,
                "mail_server_password" => $request->password,
                "mail_server_port" => $request->port,
                "mail_server_protocol" => $request->protocol,
                "app_login_url" => Config::get('email-claim-configuration.app_url_incoming_mail') . route('passport.token', null, false),
                "app_id" => $emailClaimConfiguration->subscriber_id,
                "app_login_params" => [
                    "grant_type" => $params['grant_type'],
                    "client_id" => $params['client_id'],
                    "client_secret" => $params['client_secret'],
                ]
            ];


            $response = $httpClient->put($params['api_subscriber'], $requestData)->json();

            if ($response == null) {
                $response = Http::put($params['api_subscriber'], $requestData)->json();
            }

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

        $subscriber = $emailClaimConfiguration ? $this->updateSubscriber($request, $emailClaimConfiguration, $routeName) : $this->subscriber($request, $routeName);

        if ($subscriber['error']) {
            try {
                Log::debug("subscribtion error", $subscriber);
            } catch (\Exception $exception) {
                Log::info($subscriber['message']);
            }

            return [
                "error" => true,
                "message" => __('messages.invalid_params', [], getAppLang()),
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

                $mailSubject = $email['header']['subject'];
                $mailContent = $email['plainMessage'];

                $references = array_unique(array_merge(extractClaimRefs($mailContent), extractClaimRefs($mailSubject)));
                $number =  extractPhoneNumber($mailContent);

                if (!empty($references)) {
                    foreach ($references as $reference) {
                        $error = true;
                        if (claimsExists($reference)){
                            if (!empty($number)){
                                $claim = Claim::query()
                                    ->with('claimer')
                                    ->where('reference',$reference)
                                    ->first();

                                if ($claim){
                                    $claimer = $claim->claimer;
                                    $claimer->update(['telephone' => $number]);
                                    $error = false;
                                }

                            }
                        }
                    }
                }else{
                    $claim = $this->getDataIncomingEmail($email, $typeText);

                    if (!$storeClaim = $this->storeClaim($claim, $status, $configuration)) {
                        $error = true;
                    }
                }

            } catch (\Exception $e) {
                $error = true;
                Log::debug($e);
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
            "plain_text_description" => $email['plainMessage'],
            "attachments" => $email["attachments"]
        ];
    }

    protected function storeClaim($claim, $status, $configuration)
    {
        try {

            if (!$identity = $this->identityVerified($claim)) {
                $identity = Identite::create([
                    "firstname" => $claim['firstname'],
                    "lastname" => $claim['lastname'],
                    "email" => [$claim['address']],
                ]);
            }

            $claimStore = Claim::create([
                'reference' => $this->createReference($configuration->institution_id),
                'description' => $claim['description'],
                'plain_text_description' => $claim['plain_text_description'],
                'status' => $status,
                'claimer_id' => $identity->id,
                "institution_targeted_id" => $configuration->institution_id,
                "request_channel_slug" => "email",
                "response_channel_slug" => "email"
            ]);

            for ($i = 0; $i < sizeof($claim['attachments']); $i++) {
                $save_img = $this->base64SaveImg($claim['attachments'][$i], 'claim-attachments/', $i);
                $claimStore->files()->create(['title' => "Incoming mail attachment " . $claimStore->reference, 'url' => $save_img['link']]);
            }

            $claimStore->load('claimer', 'institutionTargeted');
            // send notification to claimer
            if (!is_null($claimStore->claimer)) {
                $claimStore->claimer->notify(new AcknowledgmentOfReceipt($claimStore));
            }

            // send notification to pilot
            if (!is_null($claimStore->institutionTargeted)) {
                if (!is_null($this->getInstitutionPilot($claimStore->institutionTargeted))) {
                    $this->getInstitutionPilot($claimStore->institutionTargeted)->notify(new RegisterAClaim($claimStore));
                }
            }

            return true;

        } catch (\Exception $exception) {
            Log::info("-----------------incoming mail-------------------------");
            Log::debug($exception);
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

    protected function getConfiguration($institutionId)
    {
        return EmailClaimConfiguration::query()
            ->where('institution_id',$institutionId)
            ->first();
    }
}