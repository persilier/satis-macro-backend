<?php
namespace Satis2020\ServicePackage\Exceptions;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Traits\ApiResponser;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class Handler
{
    use ApiResponser;

    /**
     * Render an exception into an HTTP response.
     *
     * @param  Request  $request
     * @param Exception $exception
     * @return Response|\Symfony\Component\HttpFoundation\Response|bool
     */
    public function render($request, Exception $exception)
    {
        if ($exception instanceof ValidationException){
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if ($exception instanceof ModelNotFoundException){
            $modelName = Str::lower(class_basename($exception->getModel()));

            return $this->errorResponse("Does not exist any {$modelName} with the specified identificator", 404);
        }

        if ($exception instanceof AuthenticationException){
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof AuthorizationException){
            return $this->errorResponse($exception->getMessage(), 403);
        }

        if ($exception instanceof NotFoundHttpException){
            return $this->errorResponse('The specified URL cannot be found', 404);
        }

        if ($exception instanceof MethodNotAllowedHttpException){
            return $this->errorResponse('The specified method for the request is invalid', 405);
        }

        if($exception instanceof HttpException){
            return $this->errorResponse($exception->getMessage(), $exception->getStatusCode());
        }

        if($exception instanceof SecureDeleteException){
            $modelName = Str::lower(class_basename($exception->getModel()));

            return $this->errorResponse("Impossible de supprimer cette instance de {$modelName}. Elle est liée à d'autre(s) ressource(s)", 404);
        }

        if($exception instanceof QueryException){
            $errorCode = $exception->errorInfo[1];

            if($errorCode == 1451){
                return $this->errorResponse(
                    'Cannot remove this resource permanently. It is related with any other resource',
                    409
                );
            }
        }

        if($exception instanceof TokenMismatchException){
            return redirect()->back()->withInput($request->input());
        }

        if($exception instanceof RetrieveDataUserNatureException){
            return $this->errorResponse($exception->getMessage(), $exception->getCode());
        }

        if($exception instanceof TwoSessionNotAllowed){
            $response = [
                'status'=>$exception->getCode(),
                'message'=>$exception->getMessage()
            ];
            return \response()->json($response,$exception->getCode());
        }

        if($exception instanceof CustomException){
            return $this->errorResponse($exception->getData(), $exception->getCode());
        }

        return false;

    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param ValidationException $e
     * @param  Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $errors = $e->validator->errors()->getMessages();

        if($this->isFrontend($request)){
            return $request->ajax() ? response()->json($errors, 422) : redirect()
                ->back()
                ->withInput($request->input())
                ->withErrors($errors);
        }

        return $this->errorResponse($errors, 422);
    }

    /**
     * Convert an authentication exception into a response.
     *
     * @param  Request  $request
     * @param AuthenticationException $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {

        if($this->isFrontend($request)){
            return redirect()->guest('login');
        }

        return  $this->errorResponse('Unauthenticated', 401);
    }

    /**
     * Return true if request is from a browser and false if it's from an API client-from-my-institution
     *
     * @param  Request  $request
     * @return bool
     */
    private function isFrontend($request)
    {
        return $request->acceptsHtml() && collect($request->route()->middleware())->contains('web');
    }

    public function report(Exception $exception)
    {
        // Kill reporting if this is an "access denied" (code 9) OAuthServerException.
        if ($exception instanceof \League\OAuth2\Server\Exception\OAuthServerException && $exception->getCode() == 9) {
            return;
        }

    }
}
