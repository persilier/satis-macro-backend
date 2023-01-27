<?php
namespace Satis2020\UnitPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;

class Unit extends JsonResource
{
    /** Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'description'    => $this->description,
            'unit_type_id'   => $this->unit_type_id,
            'others'         => $this->others,
        ];
    }

}