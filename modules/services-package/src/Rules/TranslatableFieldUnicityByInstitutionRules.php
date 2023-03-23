<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class TranslatableFieldUnicityByInstitutionRules implements Rule
{

    protected $table;
    protected $column;
    protected $idColumn;
    protected $except;
    protected $message;
    protected $institution_id;

    public function __construct($table, $column, $idColumn = null, $except = null, $institution_id = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->idColumn = $idColumn;
        $this->except = $except;
        $this->institution_id = $institution_id;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */

    public function passes($attribute, $value)
    {
        $this->message = "{$value} is already used";
        $value = strtolower($value);
        $institution_id = $this->institution_id;

        try {

            return DB::table("{$this->table}")
                ->whereNull("deleted_at")
                ->get()
                ->search(function ($item, $key) use ($value, $institution_id) {
                    return is_null($this->except)
                        ? $value == strtolower(json_decode($item->{$this->column})->{app()->getLocale()}) &&  $institution_id == $item->institution_id
                        : $item->{$this->idColumn} != $this->except && $value == strtolower(json_decode($item->{$this->column})->{app()->getLocale()}) &&  $institution_id == $item->institution_id;
                }) === false;
        } catch (Exception $exception) {
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return $this->message;
    }
}
