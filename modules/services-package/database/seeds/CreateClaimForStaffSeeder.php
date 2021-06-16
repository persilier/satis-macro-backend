<?php

namespace Satis2020\ServicePackage\Database\Seeds;

use Carbon\Carbon;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimCategory;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Currency;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Relationship;
use Satis2020\ServicePackage\Models\SeverityLevel;
use Satis2020\ServicePackage\Models\Treatment;
use Satis2020\ServicePackage\Models\Unit;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\User;
use Satis2020\ServicePackage\Models\UnitType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class CreateClaimForStaffSeeder extends Seeder
{
    function claimData($faker, $channel, $currency, $staffID, $institutionId, $claim_objectId, $clientId, $accountId,
                       $with_relationshipId, $unitId, $with_client = true, $with_relationship = false){

        $data = [
            'reference' => date('Y') . date('m') . '-' . $faker->randomNumber(6, true),
            'description' => $faker->text,
            'claim_object_id'=> $claim_objectId,
            'institution_targeted_id' => $institutionId,
            'request_channel_slug' => $channel,
            'response_channel_slug' => $channel,
            'unit_targeted_id' => $unitId,
            'event_occured_at' => Carbon::now(),
            'completed_by' => $staffID,
            'completed_at' => Carbon::now(),
            'amount_disputed' => $faker->buildingNumber,
            'amount_currency_slug' => $currency,
            'is_revival' => 0,
            'created_by' => $staffID,
            'status' => 'full',
            'claimer_expectation' => $faker->text,
            'claimer_id' => $clientId
        ];

        if ($with_client) {
            $data['account_targeted_id'] = $accountId;
        }

        if ($with_relationship) {
            $data['relationship_id'] = $with_relationshipId;
        }

        return $data;
    }

    function treatmentData($claim, $unitId){

        $treatment = Treatment::create([
            'claim_id' => $claim->id,
            'transferred_to_targeted_institution_at' => Carbon::now(),
            'transferred_to_unit_at' => Carbon::now(),
            'responsible_unit_id' => $unitId
        ]);

        $claim->update([
            'active_treatment_id' => $treatment->id
        ]);

        return true;
    }

    function storClaim($institutionId){
        $faker = Faker::create();

        // données necessaire à l'enregistrement d'une reclammation
        {
            if(!$channel = Channel::where('slug', 'sms')->first()->slug){
                $cha = Channel::create([
                    'slug' =>'sms',
                    'name' => 'SMS',
                    'is_editable' => 0,
                    'is_response'=> 1
                ]);
                $channel = $cha->slug;
            }

            if(!$currency = Currency::first()->slug){
                $cur = Currency::create([
                    'slug' =>'cfa',
                    'iso_code' => 'ISO-CFA',
                    'name' => 'CFA'
                ]);
                $currency = $cur->slug;
            }

            $category_client = CategoryClient::create([
                'name' => $faker->name,
                'description' => $faker->text
            ]);

            $account_type = AccountType::create([
                'name' => $faker->name,
                'description' => $faker->text
            ]);

            $severity = SeverityLevel::create([
                'name' => $faker->name,
                'description' => $faker->text
            ]);

            $claim_category = ClaimCategory::create([
                'name' => $faker->name,
                'description' => $faker->text
            ]);

            $claim_object = ClaimObject::create([
                'name' => $faker->name,
                'description' => $faker->text,
                'time_limit' => 2,
                'severity_levels_id' => $severity->id,
                'claim_category_id' => $claim_category->id
            ]);
        }

        $staffs = Staff::with('identite')->whereHas('identite', function ($query){
            $query->has('user');
        })->where('institution_id', $institutionId)->get();

        // Enregistrement des réclammations
        foreach ($staffs as $staff){

            $unit = $staff->load('unit')->unit;

            for($n=0; $n < 5 ; $n++){
                // enregistrement des clients pour l'institution
                {

                    $identite = Identite::create([
                        'firstname' => $faker->firstNameMale,
                        'lastname' => $faker->name,
                        'sexe' => 'M',
                        'telephone' => [$faker->phoneNumber],
                        'email' => [$faker->unique()->safeEmail],
                        'ville' => $faker->city,
                    ]);

                    $client = Client::create([
                        'identites_id' => $identite->id
                    ]);

                    $clientInstitution = ClientInstitution::create([
                        'category_client_id'  => $category_client->id,
                        'client_id' => $client->id,
                        'institution_id'  => $institutionId
                    ]);

                    $account = Account::create([
                        'client_institution_id' => $clientInstitution->id,
                        'account_type_id'  => $account_type->id,
                        'number'  => $faker->creditCardNumber
                    ]);


                }


                // enregistrement des réclammation pour les clients
                {
                    $claim = Claim::create($this->claimData($faker, $channel, $currency, $staff->id, $institutionId, $claim_object->id, $identite->id, $account->id,
                        false, $unit->id));

                    $this->treatmentData($claim, $unit->id);

                }
            }
        }
        return true;
    }


    function storClaimHub($institutionId){
        $faker = Faker::create();

        // données necessaire à l'enregistrement d'une reclammation
        {
            $channel = Channel::where('slug', 'sms')->first()->slug;
            $currency = Currency::first()->slug;

            $severity = SeverityLevel::create([
                'name' => $faker->name,
                'description' => $faker->text
            ]);

            $claim_category = ClaimCategory::create([
                'name' => $faker->name,
                'description' => $faker->text
            ]);

            $claim_object = ClaimObject::create([
                'name' => $faker->name,
                'description' => $faker->text,
                'time_limit' => 2,
                'severity_levels_id' => $severity->id,
                'claim_category_id' => $claim_category->id
            ]);
        }

        $staffs = Staff::with('identite')->whereHas('identite', function ($query){
            $query->has('user');
        })->where('institution_id', $institutionId)->get();


        // Enregistrement des réclammations
        foreach ($staffs as $staff){

            $unit = $staff->load('unit')->unit;

            for($n=0; $n < 5 ; $n++){
                // enregistrement des clients pour l'institution
                {

                    $identite = Identite::create([
                        'firstname' => $faker->firstNameMale,
                        'lastname' => $faker->name,
                        'sexe' => 'M',
                        'telephone' => [$faker->phoneNumber],
                        'email' => [$faker->unique()->safeEmail],
                        'ville' => $faker->city,
                    ]);

                    $relationship = Relationship::create([
                        'name' => $faker->name,
                        'description' => $faker->text
                    ]);

                }


                // enregistrement des réclammation pour les clients
                {
                    $claim = Claim::create($this->claimData($faker, $channel, $currency, $staff->id, $institutionId, $claim_object->id, $identite->id, false,
                        $relationship->id, $unit->id, false, true ));

                    $this->treatmentData($claim, $unit->id);

                }
            }
        }
        return true;
    }


    public function run()
    {
        $nature = env('APP_NATURE');



        if($nature === 'MACRO'){

            // holding
            $this->storClaim('3d7f426e-494a-4650-a615-315db1b38c52');

            // filial
            $this->storClaim('b99a6d22-4af1-4a8a-9589-81468f5c020b');
        }

        if($nature === 'PRO'){
            // pro
            $this->storClaim('43ebf6c0-be03-4881-8196-59d476f75c9e');
        }

        if($nature === 'HUB'){
            // observatory
            $this->storClaimHub('e52e6a29-cfb3-4cdb-9911-ddaed1f17145');

            // membre
            $this->storClaimHub('74e98a2d-35ac-472e-911d-190f5a1d3fd6');
        }
    }
}
