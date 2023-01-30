<?php


namespace Satis2020\ServicePackage\Traits;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Channel;
use Satis2020\ServicePackage\Models\Claim;
use Satis2020\ServicePackage\Models\ClaimObject;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Rules\AccountValidationForImportClaimRules;
use Satis2020\ServicePackage\Rules\ChannelIsForResponseRules;
use Satis2020\ServicePackage\Rules\InstitutionValidationForImportRules;
use Satis2020\ServicePackage\Rules\NameModelRules;
use Satis2020\ServicePackage\Rules\UnitValidationForImportClaimRules;

/**
 * Trait ImportClient
 * @package Satis2020\ServicePackage\Traits
 */
trait ImportClaim
{

    /**
     * @param $row
     * @param $keyRow
     * @return mixed
     */
    public function formatDateEvent($row, $keyRow)
    {
        if(array_key_exists($keyRow, $row)) {
            $value = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[$keyRow]));
            $row[$keyRow] = $value;
        }

        return $row;
    }

    /**
     * @param $request
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return mixed
     */
    public function rules($request, $with_client = true, $with_relationship = false, $with_unit = true){

        $rules = $this->rulesIdentite();
        $rules['institution_concernee'] = ['required','exists:institutions,acronyme', new InstitutionValidationForImportRules($this->myInstitution, $this->institution()->id)];
        $rules['objet_reclamation'] = ['required', new NameModelRules(['table' => 'claim_objects', 'column' => 'name'])];
        $rules['canal_reception_slug'] = 'required|exists:channels,slug';
        $rules['canal_reponse_slug'] = ['required', 'exists:channels,slug', function($attribute, $value, $fail){

            $channel = Channel::where('slug', $value)->first();
            if (empty($channel) || ($channel->is_response != '1')) {
                $fail($attribute . ' attribute is not a response channel');
            }
        }];
        $rules['lieu'] = 'nullable|string';
        $rules['montant_reclame'] = 'nullable|integer|min:1';
        $rules['devise_slug'] = ['nullable', 'exists:currencies,slug', Rule::requiredIf(!is_null($request['montant_reclame']))];
        $rules['date_evenement'] = [
            'required',
            'date_format:Y-m-d H:i',
            function ($attribute, $value, $fail) {
                try{
                    if (Carbon::parse($value)->gt(Carbon::now())) {
                        $fail(__('validation.date_gte',["attribute"=>$attribute],getAppLang()));
                    }
                }catch (InvalidFormatException $e){

                    $fail(__('validation.date_format',['attribute'=>$attribute,':format'=>"Y-m-d H:i"],getAppLang()));
                }
            }
        ];
        $rules['description'] = 'required|string';
        $rules['attente'] = 'nullable|string';
        $rules['relance'] = ['required', Rule::in(['OUI', 'NON'])];

        if($with_client){
            $rules['numero_compte_concerne'] = ['nullable','exists:accounts,number', new AccountValidationForImportClaimRules([
                'telephone' => $request['telephone'],
                'email' => $request['email'],
                'acronyme' => $request['institution_concernee']])
            ];
        }

        if ($with_relationship) {
            $rules['relationship'] =  ['required', new NameModelRules(['table' => 'relationships', 'column' => 'name'])];
        }

        if ($with_unit) {

            $rules['unite_concernee'] = ['nullable', new UnitValidationForImportClaimRules(['table' => 'units', 'column'=> 'name', 'acronyme' => $request['institution_concernee']])];
        }

        return $rules;
    }


    /**
     * @param $request
     * @return bool
     */
    protected function identiteVerifiedImport($request){

        $identite = false;

        $verifyPhone = $this->handleInArrayUnicityVerification($request['telephone'], 'identites', 'telephone');

        $verifyEmail = $this->handleInArrayUnicityVerification($request['email'], 'identites', 'email');

        if (!$verifyPhone['status']) {

            $identite = $verifyPhone['entity'];
        }

        if (!$verifyEmail['status']) {

            $identite = $verifyEmail['entity'];
        }

        return $identite;

    }


    /**
     * @param $row
     * @param $identite
     * @param $status
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return mixed
     */
    protected function storeClaim($row, $identite, $status, $with_client = true, $with_relationship = false, $with_unit = true){

        return Claim::create($this->fillableClaim($row, $identite, $status, $with_client, $with_relationship, $with_unit));
    }


    /**
     * @param $row
     * @param $identite
     * @param $status
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return array
     */
    protected function fillableClaim($row, $identite, $status , $with_client = true, $with_relationship = false, $with_unit = true){
        $data = [
            'claimer_id'    => $identite->id,
            'reference'     => $this->createReference($row['institution_concernee']),
            'description'   => $row['description'],
            'claim_object_id' => $row['objet_reclamation'],
            'institution_targeted_id'   => $row['institution_concernee'],
            'request_channel_slug' => strtolower($row['canal_reception_slug']),
            'response_channel_slug' => strtolower($row['canal_reponse_slug']),
            'event_occured_at' => $row['date_evenement'],
            'amount_disputed' => $row['montant_reclame'],
            'amount_currency_slug' => $row['devise_slug'],
            'claimer_expectation' => $row['attente'],
            'is_revival' => $row['relance'],
            'time_limit' => ClaimObject::find($row['objet_reclamation'])->time_limit,
            'created_by' => $this->staff()->id,
            'status' => $status
        ];



        if($with_client){

            $data['account_targeted_id'] = $row['numero_compte_concerne'];
        }

        if($with_relationship){

            $data['relationship_id'] = $row['relationship'];

        }

        if($with_unit){

            $data['unit_targeted_id'] = $row['unite_concernee'];
        }

        if($status == 'full'){
            $data['completed_by'] = $data['created_by'];
            $data['completed_at'] =  Carbon::now();
        }

        return $data;
    }


    /**
     * @param $acronyme
     * @return string
     */
    protected function createReference($acronyme)
    {
        $institutionTargeted = Institution::with('institutionType')->where('id', $acronyme)->first();

        $appNature = substr($this->getAppNature($institutionTargeted->id), 0, 2);

        $claimsNumber = Claim::withTrashed()
                ->whereBetween('created_at', [
                    Carbon::now()->startOfYear()->format('Y-m-d H:i:s'),
                    Carbon::now()->endOfYear()->format('Y-m-d H:i:s')
                ])
                ->where('institution_targeted_id', $institutionTargeted->id)
                ->get()
                ->count() + 1;

        $formatClaimsNumber = str_pad("{$claimsNumber}", 6, "0", STR_PAD_LEFT);

        return 'SATIS' . $appNature . '-' . date('Y') . date('m') . $formatClaimsNumber . '-' . $institutionTargeted->acronyme;
    }


    /**
     * @param $request
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return string
     */
    protected function getStatus($request, $with_client = true, $with_relationship = false, $with_unit = true)
    {
        $requirements = ClaimObject::with('requirements')
            ->where('id', $request['objet_reclamation'])
            ->firstOrFail()
            ->requirements
            ->pluck('name');
        $rules = collect([]);

        foreach ($requirements as $requirement) {
            $rules->put($requirement, 'required');
        }

        $status = 'full';

        $validator = Validator::make($this->getData($request,$with_client,$with_relationship,$with_unit), $rules->all());

        if ($validator->fails()) {

            $status = 'incomplete';

        }

        return $status;
    }


    /**
     * @param $request
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return array
     */
    protected function getData($request, $with_client = true, $with_relationship = false, $with_unit = true)
    {
        $data = [
            'description' => $request['institution_concernee'],
            'claim_object_id'  => $request['objet_reclamation'],
            'institution_targeted_id' => $request['institution_concernee'],
            'request_channel_slug' => $request['canal_reception_slug'],
            'response_channel_slug' => $request['canal_reponse_slug'],
            'event_occured_at' => $request['date_evenement'],
            'amount_disputed' => $request['montant_reclame'],
            'is_revival' => $request['relance'],
            'claimer_expectation' => $request['attente'],
        ];

        if (!is_null($request['montant_reclame'])) {
            if ($request['montant_reclame'] >= 1) {
                $data['amount_currency_slug'] = $request['devise_slug'];
            }
        }

        if ($with_client) {

            $data['account_targeted_id'] = $request['numero_compte_concerne'];
        }

        if ($with_relationship) {

            $data['relationship_id'] = $request['relationship'];
        }

        if($with_unit){
            $data['unit_targeted_id'] = $request['unite_concernee'];
        }

        return $data;
    }


    /**
     * @param $data
     * @param bool $with_client
     * @param bool $with_relationship
     * @param bool $with_unit
     * @return
     */
    protected function recupIdsData($data, $with_client = true ,$with_relationship = false, $with_unit = true){

        $data = $this->getIdInstitution($data, 'institution_concernee', 'acronyme');

        if($with_unit){

            $data = $this->getIds($data, 'units', 'unite_concernee', 'name');

        }

        if($with_relationship){

            $data = $this->getIds($data, 'relationships', 'relationship', 'name');
        }

        $data = $this->getIds($data, 'claim_objects', 'objet_reclamation', 'name');

        if($with_client){

            $data = $this->getAccountIds($data, 'accounts', 'numero_compte_concerne', 'number');

        }

        if(strtolower($data['relance']) =='oui'){

            $data['relance'] = true;

        }else{

            $data['relance'] = false;
        }

        return $data;
    }

}
