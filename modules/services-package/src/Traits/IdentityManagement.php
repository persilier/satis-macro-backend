<?php


namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Models\Identite;

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
}