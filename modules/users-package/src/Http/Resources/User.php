<?php
namespace Satis2020\UserPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Satis2020\ServicePackage\Models\Staff;
use Satis2020\UserPackage\Http\Resources\Identite as IdentiteResource;
use Satis2020\UserPackage\Http\Resources\Role as RoleResource;
class User extends JsonResource
{

    /** Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id'    => $this->id,
            'username' => $this->username,
            'identite' => new IdentiteResource($this->identite),
            'role'     => new RoleResource($this->roles->first()),
        ];
    }

}

