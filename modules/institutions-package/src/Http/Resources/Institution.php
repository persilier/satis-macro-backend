<?php
namespace Satis2020\InstitutionPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class Institution extends JsonResource
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
            'slug' => $this->slug,
            'name' => $this->name,
            'acronyme' => $this->acronyme,
            'iso_code' => $this->iso_code,
            'logo' => empty($this->logo) ? null : asset('storage' . $this->logo) ,
            'orther_attributes' => $this->orther_attributes
        ];
    }

}

