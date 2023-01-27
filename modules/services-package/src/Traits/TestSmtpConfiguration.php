<?php

namespace Satis2020\ServicePackage\Traits;

use Swift_Mailer;
use Swift_SmtpTransport;

trait TestSmtpConfiguration
{
    protected function testSmtp($host, $port, $protocol, $username, $password)
    {
        try{

            $transport = new Swift_SmtpTransport($host, $port, $protocol);
            $transport->setUsername($username);
            $transport->setPassword($password);
            $mailer = new Swift_Mailer($transport);
            $mailer->getTransport()->start();
            return [
                'error' => false,
                'message' => "Test de mail envoyé avec succès."
            ];
        } catch (\Swift_TransportException $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
    }
}

