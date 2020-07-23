<?php


namespace Satis2020\ServicePackage\Traits;


/**
 * Trait Telephone
 * @package Satis2020\ServicePackage\Traits
 */
trait Telephone
{
    public function removeSpaces($phones)
    {
        $collection = collect([]);
        foreach ($phones as $phone){
            $collection->push(preg_replace("/\s+/", "", $phone));
        }
        return $collection->all();
    }
}