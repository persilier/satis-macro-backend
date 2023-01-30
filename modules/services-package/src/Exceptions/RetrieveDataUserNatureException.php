<?php


namespace Satis2020\ServicePackage\Exceptions;
use Exception;
class RetrieveDataUserNatureException extends Exception
{

    public function __construct($message, $code = 404)
    {
        parent::__construct();

        $this->message = $message;
        $this->code = $code;
    }

}
