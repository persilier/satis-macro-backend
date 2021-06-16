<?php
namespace Satis2020\SeverityLevelPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class SeverityLevel extends JsonResource
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
            'time_limit' => $this->time_limit,
            'description' => $this->description,
            'others' => $this->others,
        ];
    }


}

