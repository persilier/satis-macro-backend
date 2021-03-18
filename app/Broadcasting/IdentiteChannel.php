<?php

namespace App\Broadcasting;

use Satis2020\ServicePackage\Models\User;

/**
 * Class IdentiteChannel
 * @package App\Broadcasting
 */
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
     * @param  \Satis2020\ServicePackage\Models\User  $user
     * @return array|bool
     */
    public function join(User $user)
    {
        //
    }
}
