<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class FieldUnicityRules implements Rule
{

    protected $table;
    protected $column;
    protected $idColumn;
    protected $except;
    protected $message;

    public function __construct($table, $column, $idColumn = null, $except = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->idColumn = $idColumn;
        $this->except = $except;
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

        try {

            return DB::table("{$this->table}")
                    ->whereNull("deleted_at")
                    ->get()
                    ->search(function ($item, $key) use ($value) {
                        return is_null($this->except)
                            ? $value == $item->{$this->column}
                            : $item->{$this->idColumn} != $this->except && $value == $item->{$this->column};
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
