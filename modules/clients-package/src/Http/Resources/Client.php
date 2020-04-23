<?php


namespace Satis2020\ClientPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'lastname'              => $this->lastname,
            'firstname'             => $this->firstname,
            'gender'                => $this->gender,
            'phone'                 => $this->phone,
            'email'                 => $this->email,
            'ville'                 => $this->ville,
            'id_card'               => $this->id_card,
            'is_client'             => $this->is_client,
            'account_number'        => $this->account_number,
            'type_clients_id'       => $this->type_clients_id,
            'category_clients_id'   => $this->category_clients_id,
            'units_id'              => $this->units_id,
            'institutions_id'       => $this->institutions_id,
            'others'                => $this->others,
        ];
    }

}