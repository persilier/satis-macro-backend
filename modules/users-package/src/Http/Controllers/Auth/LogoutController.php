<?php
namespace Satis2020\UserPackage\Http\Controllers\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Laravel\Passport\Http\Controllers\HandlesOAuthErrors;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Parser as JwtParser;
use League\OAuth2\Server\AuthorizationServer;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Services\UserService;
use Satis2020\ServicePackage\Traits\IdentityManagement;
use Satis2020\ServicePackage\Traits\VerifyUnicity;

/**
 * Class AuthController
 * @package Satis2020\UserPackage\Http\Controllers\Auth
 */
class LogoutController extends ApiController
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
        $this->middleware('auth:api');
        $this->middleware('permission:logout-user-my-institution')->only(['store']);

    }


    /**
     * Log the user out the application
     *
     * @param Request $request
     * @param UserService $userService
     * @return JsonResponse
     * @throws ValidationException
     * @throws RetrieveDataUserNatureException
     */
    public function store(Request $request,UserService $userService)
    {
        $this->validate($request,[
            'id'=>['required','exists:users,id']
        ]);

        $user = $userService->getUserById($request->id);

        $this->activityLogService->store(
            'Logout',
            $this->institution()->id,
            $this->activityLogService::LOGOUT,
            'user',
            $this->user(),
            $user
        );

        $user->tokens()->latest()->limit(1)->get()->map(function ($token) {
            $token->revoke();
        });

        return $this->showMessage("Déconnexion réussie de l'utilisateur.");
    }


}
