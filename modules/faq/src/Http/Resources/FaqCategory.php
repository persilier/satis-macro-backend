<?php
namespace Satis2020\FaqPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class FaqCategory extends JsonResource
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
            'slug' => $this->slug
        ];
    }

}

