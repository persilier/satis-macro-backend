<?php
namespace Satis2020\UserPackage\Http\Resources;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Satis2020\ServicePackage\Traits\MetaWithResources;

class RoleCollection extends ResourceCollection
{
    use MetaWithResources;
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'roles' => $this->collection,
            'header' => $this->getHeader('rfscrefezf')
        ];
    }
}