<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;

/**
 * Trait VerifyUnicity
 * @package Satis2020\ServicePackage\Traits
 */
trait VerifyUnicity
{

    protected function handleUnicityWithoutConflit($request, $idColumn = null, $idValue = null)
    {
        $identite = null;

        $verifyPhone = $this->handleInArrayUnicityVerification($request->telephone, 'identites', 'telephone', $idColumn, $idValue);

        $verifyEmail = $this->handleInArrayUnicityVerification($request->email, 'identites', 'email', $idColumn, $idValue,);

        if (!$verifyPhone['status']) {
            $identite = $verifyPhone['entity'];
        }

        if (!$verifyEmail['status']) {
            $identite = $verifyEmail['entity'];
        }

        return $identite;
    }

    /**
     * Verify if an attribute is uniq in a table
     *
     * @param $values
     * @param $table
     * @param $column
     * @param null $idColumn
     * @param null $idValue
     * @return array
     */
    protected function handleInArrayUnicityVerification($values, $table, $column, $idColumn = null, $idValue = null, $typeColumn = null, $typeValue = null)
    {
        foreach ($values as $value) {

            $value = strtolower($value);

            $query = DB::table($table)->whereJsonContains("$column", "$value")->when($typeColumn !== null && $typeValue !== null, function ($q) use ($typeColumn, $typeValue) {
                $q->where("$typeColumn", "$typeValue");
            });


            if ($idColumn && $idValue) {
                $query = $query->whereNotIn("$idColumn", [$idValue]);
            }

            $entity = $query->first();

            if ($entity) {
                return [
                    'status' => false,
                    'conflictValue' => $value,
                    'entity' => $entity
                ];
            }
        }

        return ['status' => true];
    }

    /**
     * Verify if a staff already exist using an email address or a phone number
     *
     * @param $values
     * @param $table
     * @param $column
     * @param $attribute
     * @param null $idColumn
     * @param null $idValue
     * @return array
     */
    protected function handleStaffIdentityVerification($values, $table, $column, $attribute, $idColumn = null, $idValue = null)
    {
        $verify = $this->handleInArrayUnicityVerification($values, $table, $column, $idColumn, $idValue);

        if (!$verify['status']) {

            $staff = Staff::with('identite')->where('identite_id', '=', $verify['entity']->id)->first();

            if (!is_null($staff)) {
                return [
                    'status' => false,
                    'message' => 'Un Staff existe déjà avec le même ' . $attribute . ' : ' . $verify['conflictValue'],
                    'staff' => $staff,
                    'verify' => $verify
                ];
            }

            return [
                'status' => false,
                'message' => 'Nous avons retrouvé quelqu\'un avec le même ' . $attribute . ' : ' . $verify['conflictValue'] . ' que vous avez fournis! Svp, vérifiez s\'il s\'agit de la même personne que vous voulez enregistrer en tant que Staff',
                'identite' => $verify['entity'],
                'verify' => $verify
            ];
        }

        return ['status' => true];
    }

    /**
     * @param $request
     * @return void
     * @throws CustomException
     */
    protected function handleStaffPhoneNumberAndEmailVerificationStore($request)
    {
        // Staff PhoneNumber Unicity Verification
        $verifyPhone = $this->handleStaffIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone');

        if (!$verifyPhone['status']) {
            throw new CustomException($verifyPhone, 409);
        }

        // Staff Email Unicity Verification
        $verifyEmail = $this->handleStaffIdentityVerification($request->email, 'identites', 'email', 'email');

        if (!$verifyEmail['status']) {
            throw new CustomException($verifyEmail, 409);
        }
    }

    /**
     * @param $request
     * @param $idColumn
     * @param $idValue
     * @return void
     * @throws CustomException
     */
    protected function handleIdentityPhoneNumberAndEmailVerificationStore($request, $idValue = null)
    {

        if ($request->has('telephone')) {
            // Identity PhoneNumber Unicity Verification
            $verifyPhone = $this->handleInArrayUnicityVerification($request->telephone, 'identites', 'telephone', 'id', $idValue, 'type_client', $request->type_client);
            if (!$verifyPhone['status']) {
                $verifyPhone['message'] = 'Nous avons retrouvé quelqu\'un avec le numéro de téléphone : ' . $verifyPhone['conflictValue'] . ' que vous avez fournis! Svp, vérifiez s\'il s\'agit de la même personne que vous voulez enregistrer en tant que  réclamant';
                throw new CustomException($verifyPhone, 409);
            }
        }

        // Identity Email Unicity Verification
        if ($request->has('email')) {
            $verifyEmail = $this->handleInArrayUnicityVerification($request->email, 'identites', 'email', 'id', $idValue, 'type_client', $request->type_client);

            if (!$verifyEmail['status']) {
                $verifyEmail['message'] = 'Nous avons retrouvé quelqu\'un avec l\'adresse email : ' . $verifyEmail['conflictValue'] . ' que vous avez fournis! Svp, vérifiez s\'il s\'agit de la même personne que vous voulez enregistrer en tant que  réclamant';
                throw new CustomException($verifyEmail, 409);
            }
        }
    }

    protected function existIdentityPhoneNumberAndEmailVerificationStore($request, $idValue = null)
    {

        if ($request->has('telephone')) {
            // Identity PhoneNumber Unicity Verification
            $verifyPhone = $this->handleInArrayUnicityVerification($request->telephone, 'identites', 'telephone', 'id', $idValue);
            if (!$verifyPhone['status']) {
                $verifyPhone['message'] = 'Nous avons retrouvé quelqu\'un avec le numéro de téléphone : ' . $verifyPhone['conflictValue'] . ' que vous avez fournis! Svp, vérifiez s\'il s\'agit de la même personne que vous voulez enregistrer en tant que  réclamant';
                return [
                    "exist" => true,
                    "data" => $verifyPhone
                ];
            }
        }

        // Identity Email Unicity Verification
        if ($request->has('email')) {
            $verifyEmail = $this->handleInArrayUnicityVerification($request->email, 'identites', 'email', 'id', $idValue);

            if (!$verifyEmail['status']) {
                $verifyEmail['message'] = 'Nous avons retrouvé quelqu\'un avec l\'adresse email : ' . $verifyEmail['conflictValue'] . ' que vous avez fournis! Svp, vérifiez s\'il s\'agit de la même personne que vous voulez enregistrer en tant que  réclamant';
                return [
                    "exist" => true,
                    "data" => $verifyEmail
                ];
            }
        }
        return [
            "exist" => false,
        ];
    }

    /**
     * @param $request
     * @param $identite
     * @return void
     * @throws CustomException
     */
    protected function handleStaffPhoneNumberAndEmailVerificationUpdate($request, $identite)
    {
        // Staff PhoneNumber Unicity Verification
        $verifyPhone = $this->handleStaffIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', 'id', $identite->id);
        if (!$verifyPhone['status']) {
            $verifyPhone['message'] = "Nous ne pouvons pas traiter votre demande. Le numéro de téléphone " . $verifyPhone['verify']['conflictValue'] . " appartient à quelqu'un d'autre";
            throw new CustomException($verifyPhone, 409);
        }

        // Staff Email Unicity Verification
        $verifyEmail = $this->handleStaffIdentityVerification($request->email, 'identites', 'email', 'email', 'id', $identite->id);
        if (!$verifyEmail['status']) {
            $verifyEmail['message'] = "Nous ne pouvons pas traiter votre demande. L'adresse email " . $verifyEmail['verify']['conflictValue'] . " appartient à quelqu'un d'autre";
            throw new CustomException($verifyEmail, 409);
        }
    }

    /**
     * Verify the consistency between the unit and the position of a Staff
     *
     * @param $position_id
     * @param $unit_id
     * @return bool
     */
    protected function handleSameInstitutionVerification($position_id, $unit_id)
    {
        return in_array(Unit::find($unit_id)->institution->id, Position::find($position_id)->institutions->pluck('id')->all());
    }

    /**
     * Verify the consistency between the unit and the institution of a Staff
     *
     * @param $institution_id
     * @param $unit_id
     * @return void
     * @throws RetrieveDataUserNatureException
     * @throws CustomException
     */
    protected function handleUnitInstitutionVerification($institution_id, $unit_id)
    {
        try {
            $condition = Unit::findOrFail($unit_id)->institution->id !== $institution_id;
        } catch (\Exception $exception) {
            throw new RetrieveDataUserNatureException('Unable to find the unit institution');
        }

        if ($condition) {
            throw new CustomException([
                'message' => 'The unit is not linked to the institution'
            ], 409);
        }
    }


    /**
     * @param $values
     * @param $table
     * @param $column
     * @param $attribute
     * @param $idInstitution
     * @param null $idColumn
     * @param null $idValue
     * @return array
     */
    protected function handleClientIdentityVerification($values, $table, $column, $attribute, $idInstitution, $idColumn = null, $idValue = null, $typeColumn = null, $typeValue = null)
    {

        $verify = $this->handleInArrayUnicityVerification($values, $table, $column, $idColumn, $idValue,$typeColumn,$typeValue);

        if (!$verify['status']) {

            $client = Client::with(['identite', 'client_institutions'])->where(function ($query) use ($idInstitution) {

                $query->whereHas('client_institutions', function ($q) use ($idInstitution) {

                    $q->where('institution_id', $idInstitution);
                });
            })->where('identites_id', '=', $verify['entity']->id)->first();

            if (!is_null($client)) {

                return [
                    'status' => false,
                    'message' => 'Nous avons retrouvé un client avec le ' . $attribute . ' : ' . $verify['conflictValue'],
                    'identite' => $client,
                    'verify' => $verify
                ];
            }

            return [
                'status' => false,
                'message' => 'Nous avons retrouvé quelqu\'un avec le ' . $attribute . ' : ' . $verify['conflictValue'] . ' que vous avez fournis! Svp, vérifiez s\'il s\'agit de la même personne que vous voulez enregistrer en tant que client',
                'identite' => $verify['entity'],
                'verify' => $verify
            ];
        }

        return ['status' => true];
    }


    /**
     * @param $number
     * @param null $accountId
     * @return array
     */
    protected function handleAccountVerification($number, $accountId = null)
    {

        if (!$account = Account::where('number', $number)->where('id', '!=', $accountId)->first()) {

            return ['status' => true];
        }

        return [
            'status' => false,
            'message' => 'Un client existe déjà avec ce numéro de compte.',
            'account' => $account->load(
                'AccountType',
                'client_institution.category_client',
                'client_institution.client.identite'
            ),
        ];
    }
}
