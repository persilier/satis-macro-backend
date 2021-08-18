<?php
namespace Satis2020\UserPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Satis2020\ServicePackage\Traits\MetaWithResources;
class Identite extends JsonResource
{
    use MetaWithResources;

    /** Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        return [
            'id'            => $this->id,
            'firstname'     => $this->firstname,
            'lastname'      => $this->lastname,
            'sexe'          => $this->sexe,
            'telephone'     => $this->telephone,
            'email'         => $this->email,
            'id_card'       => $this->id_card,
            'ville'         => $this->ville,
            'other_attributes' => $this->other_attributes
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function with($request)
    {
        return [
            'header' => $this->getHeader('rfscrefezf')
        ];
    }
}

