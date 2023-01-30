<?php


namespace Satis2020\ServicePackage\Exceptions;
use Exception;
use Illuminate\Http\Response;

class TwoSessionNotAllowed extends Exception
{
    protected $data;

    public function __construct($message, $code = Response::HTTP_NOT_ACCEPTABLE)
    {
        parent::__construct();

        $this->message = $message;
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
