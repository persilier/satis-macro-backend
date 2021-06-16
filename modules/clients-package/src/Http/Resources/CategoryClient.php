<?php
namespace Satis2020\ClientPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Satis2020\InstitutionPackage\Http\Resources\Institution;

class CategoryClient extends JsonResource
{

    /** Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'institution' => new Institution($this->institution),
        ];
    }
}

