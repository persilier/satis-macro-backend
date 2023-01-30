<?php

namespace Satis2020\ServicePackage\Broadcasting;

use Satis2020\ServicePackage\Models\Identite;
use Satis2020\ServicePackage\Models\User;

class IdentiteChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param \Satis2020\ServicePackage\Models\User $user
     * @param Identite $identite
     * @return array|bool
     */
    public function join(User $user, Identite $identite)
    {
        return $user->identite->id === $identite->id;
    }
}
