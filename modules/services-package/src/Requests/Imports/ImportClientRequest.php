<?php

namespace Satis2020\ServicePackage\Requests\Imports;

use Illuminate\Foundation\Http\FormRequest;
/**
 * Class ImportClientRequest
 * @package Satis2020\ServicePackage\Requests
 */
class ImportClientRequest extends FormRequest
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
            'file' => 'required|file|max:2048000|mimes:xls,xlsx,csv,txt',
            'etat_update' => 'required|boolean',
            'stop_identite_exist' => 'required|boolean'
        ];
    }

}