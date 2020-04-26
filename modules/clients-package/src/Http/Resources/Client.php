<?php


namespace Satis2020\ClientPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Satis2020\InstitutionPackage\Http\Resources\Institution;
use Satis2020\UnitPackage\Http\Resources\Unit;
use Satis2020\UserPackage\Http\Resources\Identite;
class Client extends JsonResource
{
    /** Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'account_number'        => $this->account_number,
            'others'                => $this->others,
            'identite'              => New Identite($this->identite),
            'unit'                  => New Unit($this->unit),
            'institution'           => New Institution($this->institution),
            'type_client'           => New TypeClient($this->type_client),
            'category_client'       => New CategoryClient($this->category_client),
        ];
    }

}