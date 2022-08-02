<?php
namespace Satis2020\UserPackage\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laminas\Diactoros\Response as Psr7Response;
use Laravel\Passport\Http\Controllers\HandlesOAuthErrors;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\Exception\OAuthServerException;
use Psr\Http\Message\ServerRequestInterface;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Requests\LoginRequest;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Services\Auth\AuthService;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

/**
 * Class AuthController
 * @package Satis2020\UserPackage\Http\Controllers\Auth
 */
class AuthController extends ApiController
{
    use VerifyUnicity, IdentityManagement,HandlesOAuthErrors;

    /**
     * @var AuthorizationServer
     */
    private $server;
    /**
     * @var JwtParser
     */
    private $jwt;
    /**
     * @var TokenRepository
     */
    private $tokens;

    protected $activityLogService;

    public function __construct(
        AuthorizationServer $server,
        TokenRepository $tokens,
        JwtParser $jwt,
        ActivityLogService $activityLogService
    )
    {
        parent::__construct();
        $this->jwt = $jwt;
        $this->server = $server;
        $this->tokens = $tokens;
        $this->activityLogService = $activityLogService;
        $this->middleware('auth:api')->except(['store']);
    }

    /**
     * Log the user into the application
     *
     * @return UserResource
     */
    public function login()
    {
        $user = $this->user();

        return response()->json([
            'data' => $user->load('identite', 'roles'),
            'staff' => $this->staff(),
            "app-nature" => $this->nature(),
            "permissions" => $user->getPermissionsViaRoles()->pluck('name'),
            'institution'=> $this->institution()
        ],200);
    }

    /**
     * Log the user out the application
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->activityLogService->store(
            'Logout',
            $this->institution()->id,
            $this->activityLogService::LOGOUT,
            'user',
            $this->user(),
            $this->user()
        );

        $this->user()->tokens()->latest()->limit(1)->get()->map(function ($token) {
            $token->revoke();
        });
        return $this->showMessage('Déconnexion réussie de l\'utilisateur.');
    }


    /**
     * @param ServerRequestInterface $serverRequest
     * @param Request $request
     * @return mixed
     * @throws \Laravel\Passport\Exceptions\OAuthServerException
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(ServerRequestInterface $serverRequest,LoginRequest $request)
    {
        $authService = new AuthService($request);
        $AttemptsResponse = $authService->proceedAttempt();

        if($AttemptsResponse['status']!=Response::HTTP_OK){
            return \response($AttemptsResponse,$AttemptsResponse['status']);
        }

        try
        {
            $convertedResponse =  $this->convertResponse(
                $this->server->respondToAccessTokenRequest($serverRequest, new Psr7Response)
            );
            $authService->resetAttempts(true);
            $content = json_decode($convertedResponse->getContent(),true);

            return  \response($content,Response::HTTP_OK);
        } catch (OAuthServerException $e) {
            $authService->logAttempt();
            return  \response([
                "error"=>true,
                "message"=>$e->getMessage()
            ],Response::HTTP_UNAUTHORIZED);
        }
    }
}
