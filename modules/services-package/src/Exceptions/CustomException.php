<?php


namespace Satis2020\ServicePackage\Exceptions;
use Exception;
class CustomException extends Exception
{
    protected $data;

    public function __construct($data, $code = 404)
    {
        parent::__construct();

        $this->data = $data;
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

}
