<?php


namespace Satis2020\ServicePackage\Traits;


use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Models\Position;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\ServicePackage\Models\Unit;

trait VerifyUnicity
{
    /**
     * Verify the unicity of values in a column of a table in database
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

}