<?php

namespace Satis2020\ServicePackage\Listeners;

use Satis2020\ServicePackage\Exceptions\TwoSessionNotAllowed;
use Satis2020\ServicePackage\Models\User;

class RevokeExistingTokens
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        /*$user = User::find($event->userId);
        $user->tokens()->limit(PHP_INT_MAX)->offset(1)->get()->map(function ($token) use ($user) {
           if (!$token->revoked){
               throw new TwoSessionNotAllowed("Désolé, vous êtes déjà connecté sur un autre appareil");
           }
        });*/
    }
}
