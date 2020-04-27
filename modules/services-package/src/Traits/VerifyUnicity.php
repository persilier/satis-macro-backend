<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Support\Facades\DB;
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
            $staff = Staff::with('identite')->where('identite_id', '=', $verify['entity']->id)->first();
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

    protected function handleSameInstitutionVerification($position_id, $unit_id)
    {
        return in_array(Unit::find($unit_id)->institution->id, Position::find($position_id)->institutions->pluck('id')->all());
    }


    protected function handleClientIdentityVerification($values, $table, $column, $attribute, $idColumn = null, $idValue = null)
    {
        $verify = $this->handleInArrayUnicityVerification($values, $table, $column, $idColumn, $idValue);
        if (!$verify['status']) {
            $client = Client::with('identite')->where('identites_id', '=', $verify['entity']->id)->first();
            if (!is_null($client)) {
                return [
                    'status' => false,
                    'message' => 'A Client already exist with the ' . $attribute . ' : ' . $verify['conflictValue'],
                    'client' => $client,
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

}