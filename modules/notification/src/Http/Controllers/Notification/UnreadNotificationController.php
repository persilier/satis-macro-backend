<?php

namespace Satis2020\Notification\Http\Controllers\Notification;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Satis2020\ServicePackage\Http\Controllers\ApiController;
use Satis2020\ServicePackage\Models\Metadata;
use Satis2020\ServicePackage\Models\Claim;

class UnreadNotificationController extends ApiController
{

    use \Satis2020\ServicePackage\Traits\Notification;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth:api');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $unreadNotifications = Auth::user()->identite->unreadNotifications->filter(function ($value, $key) {

            list($empty, $type) = explode("Satis2020\ServicePackage\Notifications\\", $value->type);

            try{

                $claim = Claim::findOrFail(($value->data)['claim']['id']);

            }catch(\Exception $exception){
                $value->markAsRead();
                return false;
            }
            
            try{

                if(!in_array($claim->status, $this->getNotificationStatus($type))){
                    $value->markAsRead();
                    return false;
                }

                if($type == 'RegisterAClaim' && ($value->data)['claim']['status'] === 'incomplete' && $claim->status !== 'incomplete'){
                    $value->markAsRead();
                    return false;
                }

            }catch(\Exception $exception){
                return true;
            }

            return true;

        })->values();

        return response()->json($unreadNotifications, 200);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        $rules = [
            'notifications' => 'required|array',
            'notifications.*' => 'required|exists:notifications,id'
        ];

        $this->validate($request, $rules);

        $canReloadCollection = collect(['canReload' => false]);

        $unreadNotifications = Auth::user()->identite->unreadNotifications->filter(function ($value, $key) use($request, $canReloadCollection) {

            if(in_array($value->id, $request->notifications)){

                if(count($request->notifications) === 1){

                    list($empty, $type) = explode("Satis2020\ServicePackage\Notifications\\", $value->type);

                    try{

                        $claim = Claim::findOrFail(($value->data)['claim']['id']);

                        if(in_array($claim->status, $this->getNotificationStatus($type))){
                            $canReloadCollection->put('canReload', true);
                        }

                        if($type == 'RegisterAClaim' && ($value->data)['claim']['status'] === 'incomplete' && $claim->status !== 'incomplete'){
                            $canReloadCollection->put('canReload', false);
                        }

                    }catch(\Exception $exception){
                        $canReloadCollection->put('canReload', false);
                    }

                }

                $value->markAsRead();

                return false;

            }

            return true;

        })->values();

        return response()->json(['unreadNotifications' => $unreadNotifications, 'canReload' => $canReloadCollection->get('canReload')], 201);
    }


}
