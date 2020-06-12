<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;

trait VerifyUnicity
{
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
    protected function handleInArrayUnicityVerification($values, $table, $column, $idColumn = null, $idValue = null)
    {
        foreach ($values as $value) {

            $collection = DB::table($table)
                ->whereNull('deleted_at')
                ->get();

            $search = $collection->search(function ($item, $key) use ($value, $column, $idColumn, $idValue) {
                return is_null($idValue)
                    ? in_array($value, json_decode($item->{$column}))
                    : $item->{$idColumn} !== $idValue && in_array($value, json_decode($item->{$column}));
            });

            if ($search !== false) return ['status' => false, 'conflictValue' => $value, 'entity' => $collection->get($search)];
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
            $staff = Staff::with('identite')->where('identite_id', '=', $verify['entity']->id)->firstOrFail();
            if (!is_null($staff)) {
                return [
                    'status' => false,
                    'message' => 'A Staff already exist with the ' . $attribute . ' : ' . $verify['conflictValue'],
                    'staff' => $staff,
                    'verify' => $verify
                ];
            }
            return [
                'status' => false,
                'message' => 'We found someone with the ' . $attribute . ' : ' . $verify['conflictValue'] . ' that you provide! Please, verify if it\'s the same that you want to register as a Staff',
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
        // Identity PhoneNumber Unicity Verification
        $verifyPhone = $this->handleInArrayUnicityVerification($request->telephone, 'identites', 'telephone', 'id', $idValue);
        if (!$verifyPhone['status']) {
            $verifyPhone['message'] = 'We found someone with the phone number : ' . $verifyPhone['conflictValue'] . ' that you provide! Please, verify if it\'s the same that you want to register as the claimer';
            throw new CustomException($verifyPhone, 409);
        }

        // Identity Email Unicity Verification
        $verifyEmail = $this->handleInArrayUnicityVerification($request->email, 'identites', 'email', 'id', $idValue);
        if (!$verifyEmail['status']) {
            $verifyEmail['message'] = 'We found someone with the email address : ' . $verifyEmail['conflictValue'] . ' that you provide! Please, verify if it\'s the same that you want to register as the claimer';
            throw new CustomException($verifyEmail, 409);
        }
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
            $verifyPhone['message'] = "We can't perform your request. The phone number ".$verifyPhone['verify']['conflictValue']." belongs to someone else";
            throw new CustomException($verifyPhone, 409);
        }

        // Staff Email Unicity Verification
        $verifyEmail = $this->handleStaffIdentityVerification($request->email, 'identites', 'email', 'email', 'id', $identite->id);
        if (!$verifyEmail['status']) {
            $verifyEmail['message'] = "We can't perform your request. The email address ".$verifyEmail['verify']['conflictValue']." belongs to someone else";
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

    protected function handleClientIdentityVerification($values, $table, $column, $attribute, $idInstitution, $idColumn = null, $idValue = null)
    {
        $verify = $this->handleInArrayUnicityVerification($values, $table, $column, $idColumn, $idValue);
        if (!$verify['status']) {
            $client = Client::with([
                    'identite', 'client_institution'
                ])->where(function ($query) use ($idInstitution){
                    $query->whereHas('client_institution', function ($q) use ($idInstitution){
                        $q->where('institution_id', $idInstitution);
                    });
                })->where('identites_id', '=', $verify['entity']->id)
                ->first();
            if (!is_null($client)) {
                return [
                    'status' => false,
                    'message' => 'A Client already exist with the ' . $attribute . ' : ' . $verify['conflictValue'],
                    'identite' => $client,
                    'verify' => $verify
                ];
            }
            return [
                'status' => false,
                'message' => 'We found someone with the ' . $attribute . ' : ' . $verify['conflictValue'] . ' that you provide! Please, verify if it\'s the same that you want to register as a Client',
                'identite' => $verify['entity'],
                'verify' => $verify
            ];
        }
        return ['status' => true];
    }


    protected function handleAccountVerification($number, $idInstitution, $accountId = null)
    {
        $account = Account::with([
            'client_institution'
        ])->where(function ($query) use ($idInstitution){
            $query->whereHas('client_institution', function ($q) use ($idInstitution){
                $q->where('institution_id', $idInstitution);
            });
        })->where('number', $number)->where('id', '!=', $accountId)->first();
        if (!is_null($account)) {
            return [
                'status' => false,
                'message' => 'A Client already exist with the ',
                'account' => $account->load(
                    'AccountType',
                    'client_institution.category_client',
                    'client_institution.client.identite'
                ),
            ];
        }
        return ['status' => true];
    }

}