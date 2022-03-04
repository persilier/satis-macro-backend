<?php

namespace Satis2020\ServicePackage\Requests\Imports;

use Illuminate\Foundation\Http\FormRequest;
/**
 * Class ImportClientRequest
 * @package Satis2020\ServicePackage\Requests
 */
class ImportStaffRequest extends FormRequest
{

    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required|file|max:2048|mimes:xls,xlsx',
            'etat_update' => 'required|boolean',
            'stop_identite_exist' => 'required|boolean'
        ];
    }

}