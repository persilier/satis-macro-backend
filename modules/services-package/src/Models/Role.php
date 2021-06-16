<?php


namespace Satis2020\ServicePackage\Models;
use \Spatie\Permission\Models\Role as SpatieRole;

/**
 * Class Role
 * @package Satis2020\ServicePackage\Models
 */
class Role extends SpatieRole
{

    public static function getRoles()
    {
        return SpatieRole::where('guard_name', 'api')
            ->get()
            ->map(function ($item, $key) {
                return ['label' => $item['name'], 'value' => $item['name']];
            })
            ->toArray();
    }
}