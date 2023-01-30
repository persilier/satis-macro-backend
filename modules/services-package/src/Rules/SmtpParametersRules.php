<?php

namespace Satis2020\ServicePackage\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Satis2020\ServicePackage\Exceptions\CustomException;
use Satis2020\ServicePackage\Models\Account;
use Satis2020\ServicePackage\Models\ClientInstitution;
use Satis2020\ServicePackage\Models\Identite;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;
use Swift_TransportException;

class SmtpParametersRules implements Rule
{

    protected $smtp_parameters;
    protected $message;

    public function __construct($smtp_parameters)
    {

        if (!array_key_exists('password', $smtp_parameters)) {
            $smtp_parameters['password'] = '';
        }

        $this->smtp_parameters = $smtp_parameters;
    }


    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */

    public function passes($attribute, $value)
    {

        try {
            $transport = (new Swift_SmtpTransport("{$this->smtp_parameters['server']}", "{$this->smtp_parameters['port']}", "{$this->smtp_parameters['security']}"))
                ->setUsername("{$this->smtp_parameters['from']}")
                ->setPassword("{$this->smtp_parameters['password']}");

            // Create the Mailer using your created Transport
            $mailer = new Swift_Mailer($transport);

            // Create a message
            $message = (new Swift_Message('Wonderful Subject'))
                ->setFrom([$this->smtp_parameters['username'] => 'John Doe'])
                ->setTo(['ulrich@dmdconsult.com', 'christian@dmdconsult.com' => 'AWASSI Guy Maurel Christian'])
                ->setBody('Here is the message itself');

            // Send the message
            return $mailer->send($message);
        } catch (Swift_TransportException $e) {
            $this->message = 'Les paramètres SMTP d\'envoie de mail renseignés ne sont pas bon.';//$e->getMessage();
            return false;
        } catch (Exception $e) {
            $this->message = 'Les paramètres SMTP d\'envoie de mail renseignés ne sont pas bon.';//$e->getMessage();
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string|array
     */
    public function message()
    {
        return $this->message;
    }

}
