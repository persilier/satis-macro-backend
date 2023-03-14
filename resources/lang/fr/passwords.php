<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Password Reminder Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are the default lines which match reasons
    | that are given by the password broker for a password update attempt
    | has failed, such as for an invalid token or invalid new password.
    |
    */

    'reset'     => 'Votre mot de passe a été réinitialisé !',
    'sent'      => 'Nous vous avons envoyé par email le lien de réinitialisation du mot de passe !',
    'throttled' => 'Veuillez patienter avant de réessayer.',
    'token'     => "Ce jeton de réinitialisation du mot de passe n'est pas valide.",
    'user'      => "Aucun utilisateur n'a été trouvé avec cette adresse email.",


    'email_password_reset_request_subject' => 'Réinitialiser votre mot de passe',
    'email_password_reset_request_line1' => 'Vous avez demandé la réinitialisation du mot de passe de votre compte.',
    'email_password_reset_request_action' => 'Réinitialiser le mot de passe',
    'email_password_reset_request_line2' => 'Si vous n\'avez pas demandé de réinitialisation de mot de passe, aucune autre action n\'est requise.',

    'email_password_reset_success_subject' => 'Votre mot de passe a été changé',
    'email_password_reset_success_line1' => 'Votre mot de passe a été modifié pour votre compte.',
    'email_password_reset_success_line2' => 'Si vous avez changé le mot de passe, aucune autre action n\'est requise.',
    'email_password_reset_success_line3' => 'Protégez votre compte, si vous n\'êtes pas l\'auteur de cette réclamation',
];
