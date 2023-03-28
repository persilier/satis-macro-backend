<?php

namespace Satis2020\ClientFromAnyInstitution\Http\Controllers\Clients;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Consts\Constants;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\Client;
use Satis2020\ServicePackage\Models\Institution;
use Satis2020\ServicePackage\Models\AccountType;
use Satis2020\ServicePackage\Models\CategoryClient;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Services\ActivityLog\ActivityLogService;
use Satis2020\ServicePackage\Traits\ClientTrait;
use Satis2020\ServicePackage\Traits\IdentiteVerifiedTrait;
use Satis2020\ServicePackage\Traits\SecureDelete;
use Satis2020\ServicePackage\Traits\VerifyUnicity;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ClientController
 * @package Satis2020\ClientFromAnyInstitution\Http\Controllers\Clients
 */
class ClientController extends ApiController
{
    use IdentiteVerifiedTrait, VerifyUnicity, ClientTrait, SecureDelete;

    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();
        $this->middleware('auth:api');
        /* $this->middleware('permission:list-client-from-any-institution')->only(['index']);
         $this->middleware('permission:store-client-from-any-institution')->only(['create', 'store']);
        $this->middleware('permission:show-client-from-any-institution')->only(['show']);
        $this->middleware('permission:update-client-from-any-institution')->only(['edit', 'update']);
        $this->middleware('permission:destroy-client-from-any-institution')->only(['destroy']);*/

        $this->activityLogService = $activityLogService;
    }


    /**
     * @return JsonResponse
     */
    public function index()
    {
        $paginationSize = \request()->query('size');
        $recherche = \request()->query('key');
        return response()->json(
            ClientInstitution::with(
                'client.identite',
                'category_client',
                'institution',
                'accounts.accountType'
            )->when($recherche != null, function (Builder $query) use ($recherche) {
                $query
                    ->leftJoin('clients', 'client_institution.client_id', '=', 'clients.id')
                    ->leftJoin('identites', 'clients.identites_id', '=', 'identites.id')
                    ->leftJoin('accounts', 'accounts.client_institution_id', '=', 'client_institution.id')
                    ->whereRaw('(`identites`.`firstname` LIKE ?)', ["%$recherche%"])
                    ->orWhereRaw('`identites`.`lastname` LIKE ?', ["%$recherche%"])
                    ->orWhereRaw('`accounts`.`number` = ?', [$recherche])
                    ->orwhereJsonContains('telephone', $recherche)
                    ->orwhereJsonContains('email', $recherche);
            })
                ->paginate($paginationSize),
            200
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     * @throws RetrieveDataUserNatureException
     */
    public function create()
    {
        return response()->json([
            'client_institutions' => ClientInstitution::with(
                'client.identite',
                'category_client',
                'institution',
                'accounts.accountType'
            )->get(),
            'institutions' => Institution::all(),
            'accountTypes' => AccountType::all(),
            'clientCategories' => CategoryClient::all()
        ], 200);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return JsonResponse
     * @throws ValidationException
     * @throws RetrieveDataUserNatureException
     * @throws CustomException
     */
    public function store(Request $request)
    {
        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rulesClient(true));

        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number);

        if (!$verifyAccount['status']) {
            throw new CustomException($verifyAccount, 409);

            //return response()->json($verifyAccount, 409);
        }
        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', $request->institution_id);

        if (!$verifyPhone['status']) {
            throw new CustomException($verifyPhone, 409);

            //return response()->json($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', $request->institution_id);

        if (!$verifyEmail['status']) {
            throw new CustomException($verifyEmail, 409);

            //return response()->json($verifyEmail, 409);
        }

        $identite = $this->storeIdentite($request);

        $client = $this->storeClient($request, $identite->id);

        $clientInstitution = $this->storeClientInstitution($request, $client->id, $request->institution_id);

        $account = $this->storeAccount($request, $clientInstitution->id);

        return response()->json($account, 201);
    }

    /**
     * Display the specified resource.
     * @param $accountId
     * @return JsonResponse
     */
    public function edit($accountId)
    {
        $account = Account::with([
            'accountType',
            'client_institution.client.identite',
            'client_institution.category_client',
            'client_institution.institution'
        ])->find($accountId);

        // verify if the account is not null and belong to the institution of the user connected
        if (is_null($account))
            return $this->errorResponse("Compte inexistant", Response::HTTP_NOT_FOUND);

        return response()->json($account, 200);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param Request $request
     * @param $clientId
     * @return JsonResponse
     * @throws ValidationException
     */
    public function show(Request $request, $clientId)
    {
        $this->validate($request, [
            'institution_id' => 'required|exists:institutions,id'
        ]);

        if (!$client = $this->getOneClient($request, $clientId)) {

            throw new CustomException("Impossible de retrouver ce client dans votre institution.");
        }

        return response()->json($client, 200);
    }


    /**
     * @param Request $request
     * @param $accountId
     * @return JsonResponse
     * @throws ValidationException
     * @throws CustomException
     */
    public function update(Request $request, $accountId)
    {
        $this->convertEmailInStrToLower($request);

        $this->validate($request, $this->rulesClient(true));

        $account = Account::with([
            'accountType',
            'client_institution.client.identite',
            'client_institution.category_client',
            'client_institution.institution'
        ])->find($accountId);

        // verify if the account is not null and belong to the institution of the user connected
        if (is_null($account))
            return $this->errorResponse("Compte inexistant", Response::HTTP_NOT_FOUND);

        $client = $account->client_institution->client;

        // Account Number Verification
        $verifyAccount = $this->handleAccountVerification($request->number, $account->id);

        if (!$verifyAccount['status']) {

            throw new CustomException($verifyAccount, 409);
        }

        // Client PhoneNumber Unicity Verification
        $verifyPhone = $this->handleClientIdentityVerification($request->telephone, 'identites', 'telephone', 'telephone', $request->institution_id, 'id', $client->identite->id);

        if (!$verifyPhone['status']) {

            $verifyPhone['message'] = "We can't perform your request. The phone number " . $verifyPhone['verify']['conflictValue'] . " belongs to someone else";
            throw new CustomException($verifyPhone, 409);
        }

        // Client Email Unicity Verification
        $verifyEmail = $this->handleClientIdentityVerification($request->email, 'identites', 'email', 'email', $request->institution_id, 'id', $client->identite->id);

        if (!$verifyEmail['status']) {

            $verifyEmail['message'] = "We can't perform your request. The email address " . $verifyEmail['verify']['conflictValue'] . " belongs to someone else";
            throw new CustomException($verifyEmail, 409);
        }

        $account->update($request->only(['number', 'account_type_id']));

        $client->identite->update($request->only(['firstname', 'lastname', 'sexe', 'telephone', 'email', 'ville', 'other_attributes']));

        $this->activityLogService->store(
            'Mise à jour des informations du compte d\'un client',
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
     */
    public function destroy($accountId)
    {
        $account = Account::with([
            'accountType',
            'client_institution.client.identite',
            'client_institution.category_client',
            'client_institution.institution'
        ])->find($accountId);

        // verify if the account is not null and belong to the institution of the user connected
        if (is_null($account))
            return $this->errorResponse("Compte inexistant", Response::HTTP_NOT_FOUND);

        $account->secureDelete('claims');

        /* $this->activityLogService->store('Suppression du numéro de compte d\'un client',
            $this->institution()->id,
            $this->activityLogService::DELETED,
            'account',
            $this->user(),
            $account
        );*/

        return response()->json($account, 201);
    }
}
