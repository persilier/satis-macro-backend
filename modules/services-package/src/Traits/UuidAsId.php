<?php
namespace Satis2020\ServicePackage\Traits;
use Illuminate\Support\Str;
trait UuidAsId
{
    protected static function bootUuidAsId()
    {
        static::creating(function ($model) {
            if (!$model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

}
