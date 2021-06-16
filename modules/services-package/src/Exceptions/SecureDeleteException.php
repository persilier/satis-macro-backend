<?php


namespace Satis2020\ServicePackage\Exceptions;
use Exception;
class SecureDeleteException extends Exception
{

    protected $model;

    public function __construct($model)
    {
        parent::__construct();

        $this->model = $model;
    }

    /**
     * Get the affected Eloquent model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }
}
