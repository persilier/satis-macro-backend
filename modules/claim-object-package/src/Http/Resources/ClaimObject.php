<?php
namespace Satis2020\ClaimObjectPackage\Http\Resources;
use Illuminate\Http\Resources\Json\JsonResource;
class ClaimObject extends JsonResource
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
            'claim_category' => $this->claimCategory,
            'severity_level' => $this->when($this->severityLevel,$this->severityLevel ,function () {
                $this->when($this->claimCategory->severityLevel, $this->claimCategory->severityLevel ,function () {
                    return null;
                });
            }),
            'time_limit' => $this->when($this->time_limit, $this->time_limit ,function () {
                $this->when($this->claimCategory->time_limit, $this->claimCategory->time_limit ,function () {
                   if(empty($this->claimCategory->severityLevel))
                        return null;
                   else
                        return $this->claimCategory->severityLevel->time_limit;
                });
            }),
            'description' => $this->description,
            'others' => $this->others,
        ];
    }


}

