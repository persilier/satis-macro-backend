<?php


namespace Satis2020\ServicePackage\Traits;

use Illuminate\Validation\Rule;
use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Rules\EmailValidationRules;

trait IdentityManagement
{
    protected function createIdentity($request)
    {
        return $identite = Identite::create($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));
    }

    protected function updateIdentity($request, $identite)
    {
        $identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));
    }

    /**
     * @return array
     */
    protected function rulesProfile(){

        return [
            'firstname' => 'required|string',
            'lastname' => 'required|string',
            'sexe' => ['required', Rule::in(['M', 'F', 'A'])],
            'telephone' => 'required|array',
            'email' => [
                'required', 'array', new EmailValidationRules,
            ],
            'ville' => 'required|string',
            'other_attributes' => 'array',
        ];
    }
}
