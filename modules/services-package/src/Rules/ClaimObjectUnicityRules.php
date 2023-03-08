<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClaimObjectUnicityRules implements Rule
{

    protected $table;
    protected $column;
    protected $request;
    protected $idColumn;
    protected $except;
    protected $message;

    public function __construct($table, $column, $request, $idColumn = null, $except = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->request = $request;
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
        $value = strtolower($value);
        try {
            return DB::table("{$this->table}")
                ->whereNull("deleted_at")
                ->get()
                ->search(function ($item, $key) use ($value) {
                    return is_null($this->except)
                        ? $value == strtolower(json_decode($item->{$this->column})->{app()->getLocale()}) && $item->claim_category_id == $this->request->claim_category_id
                        : $item->{$this->idColumn} != $this->except && $value == strtolower(json_decode($item->{$this->column})->{app()->getLocale()}) && $item->claim_category_id == $this->request->claim_category_id;
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
