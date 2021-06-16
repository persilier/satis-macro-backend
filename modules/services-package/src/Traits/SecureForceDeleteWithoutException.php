<?php

namespace Satis2020\ServicePackage\Traits;

use Satis2020\ServicePackage\Exceptions\SecureDeleteException;

trait SecureForceDeleteWithoutException
{
    /**
     * Delete only when there is no reference to other models.
     *
     * @param array $relations
     */
    public function secureForceDeleteWithoutException(String ...$relations)
    {
        $hasRelation = false;
        foreach ($relations as $relation) {
            if ($this->$relation()->count()) {
                $hasRelation = true;
                break;
            }
        }

        if ($hasRelation === false) {
            $this->forceDelete();
        }
    }
}
