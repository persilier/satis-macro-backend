<?php

namespace Satis2020\ClientFromMyInstitution\Http\Controllers\Clients;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Exceptions\RetrieveDataUserNatureException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ClientTrait;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\Search;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Symfony\Component\HttpFoundation\Response;

class ClientController extends ApiController
{
    use IdentiteVerifiedTrait, VerifyUnicity, ClientTrait, SecureDelete, Search;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {

        parent::__construct();
        $this->middleware('auth:api');
        $this->middleware('permission:list-client-from-my-institution')->only(['index']);
        $this->middleware('permission:store-client-from-my-institution')->only(['create', 'store']);
        $this->middleware('permission:show-client-from-my-institution')->only(['show']);
        $this->middleware('permission:update-client-from-my-institution')->only(['edit', 'update']);
        $this->middleware('permission:destroy-client-from-my-institution')->only(['destroy']);

        $this->activityLogService = $activityLogService;
    }


    /**
     * @return JsonResponse
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     */
    public function index()
    {
        $institution = $this->institution();
        $paginationSize = \request()->query('size');
        $recherche = \request()->query('key');


        if ($paginationSize == null)
            $paginationSize = Constants::PAGINATION_SIZE;

        $clients = $this->getAllClientByInstitution($institution->id, true, $paginationSize, $recherche);
        return response()->json($clients, 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function create(Request $request)
    {
        $institution = $this->institution();
        return response()->json([
            'accountTypes' => AccountType::all(),
            'clientCategories' => CategoryClient::all()
        ], 200);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     */
    public function store(Request $request)
    {

        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rulesClient());

        $institution = $this->institution();

        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number);

        if (!$verifyAccount['status']) {

            throw new CustomException($verifyAccount, 409);
        }

        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', $institution->id, null, null, 'type_client', $request->type_client);
        if (!$verifyPhone['status']) {

            throw new CustomException($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', $institution->id, null, null, 'type_client', $request->type_client);

        if (!$verifyEmail['status']) {

            throw new CustomException($verifyEmail, 409);
        }

        $identite = $this->storeIdentite($request);

        $client = $this->storeClient($request, $identite->id);

        $clientInstitution = $this->storeClientInstitution($request, $client->id, $institution->id);

        $account = $this->storeAccount($request, $clientInstitution->id);

        return response()->json($account, 201);
    }


    /**
     * @param $accountId
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function edit($accountId)
    {
        $institution = $this->institution();

        $account = Account::with([
            'accountType',
            'client_institution.client.identite',
            'client_institution.category_client',
            'client_institution.institution'
        ])->findOrFail($accountId);

        $account->makeVisible(['account_number']);
        $account->makeHidden(['number']);


        // verify if the account is not null and belong to the institution of the user connected
        if (is_null($account) || $account->client_institution->institution_id != $institution->id)
            return $this->errorResponse("Compte inexistant", Response::HTTP_NOT_FOUND);

        return response()->json($account, 200);
    }


    /**
     * @param Request $request
     * @param $clientId
     * @return JsonResponse
     */
    public function show(Request $request, $clientId)
    {
        $request->merge(['institution_id' => $this->institution()->id]);
        if (!$client = $this->getOneClient($request, $clientId)) {

            throw new CustomException(__('errors.client_not_found', [], app()->getLocale()));
        }

        return response()->json($client, 200);
    }


    /**
     * @param Request $request
     * @param $accountId
     * @return JsonResponse
     * @throws CustomException
     * @throws RetrieveDataUserNatureException
     * @throws ValidationException
     */
    public function update(Request $request, $accountId)
    {
        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rulesClient());

        $institution = $this->institution();

        $account = Account::with([
            'accountType',
            'client_institution.client.identite',
            'client_institution.category_client',
            'client_institution.institution'
        ])->find($accountId);

        // verify if the account is not null and belong to the institution of the user connected
        if (is_null($account) || $account->client_institution->institution_id != $institution->id) {
            return $this->errorResponse(__('errors.account_do_not_exist', [], app()->getLocale()), Response::HTTP_NOT_FOUND);
        }

        $client = $account->client_institution->client;

        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number, $account->id);

        if (!$verifyAccount['status']) {
            throw new CustomException($verifyAccount, 409);
        }

        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', $institution->id, 'id', $client->identite->id);

        if (!$verifyPhone['status']) {

            $verifyPhone['message'] = __('errors.phone_conflict', ['phone' => $verifyPhone['verify']['conflictValue']], app()->getLocale());
            throw new CustomException($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', $institution->id, 'id', $client->identite->id);

        if (!$verifyEmail['status']) {

            $verifyEmail['message'] = __('errors.email_conflict', ['email' => $verifyEmail['verify']['conflictValue']], app()->getLocale());
            throw new CustomException($verifyEmail, 409);
        }

        $account->update($request->only(['number', 'account_type_id']));

        $client->identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        $this->activityLogService->store(
            __('messages.client_updated', [], app()->getLocale()),
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'account',
            $this->user(),
            $account
        );

        return response()->json($client, 201);
    }


    /**
     * @param $accountId
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function destroy($accountId)
    {
        $institution = $this->institution();

        $account = Account::with([
            'accountType',
            'client_institution.client.identite',
            'client_institution.category_client',
            'client_institution.institution'
        ])->find($accountId);

        // verify if the account is not null and belong to the institution of the user connected
        if (is_null($account) || $account->client_institution->institution_id != $institution->id)
            return $this->errorResponse("Compte inexistant", Response::HTTP_NOT_FOUND);

        $account->secureDelete('claims');

        $this->activityLogService->store(
            'Suppression du compte d\'un client',
            $this->institution()->id,
            $this->activityLogService::UPDATED,
            'account',
            $this->user(),
            $account
        );

        return response()->json($account, 201);
    }
}
