<?php

namespace Satis2020\Webhooks\Consts;

class Event
{

    const CLAIM_REGISTERED = "claim-registered";
    const CLAIM_RECEIVED = "claim-received";
    const CLAIM_REJECTED = "claim-rejected";
    const CLAIM_CLOSED = "claim-closed";
    const CLAIM_TREATED = "claim-treated";
    const CLAIM_VALIDATED = "claim-validated";
    const CLAIM_COMPLETED = "claim-completed";
    const SATISFACTION_MEASURED = "satisfaction-measured";


    public static function getEvents()
    {
        return [
            /*[
                "label" => "Nouvelle reclamation enrégistrée", "value" => self::CLAIM_REGISTERED
            ],
            [
                "label" => "Nouvelle reclamation reçue", "value" => self::CLAIM_RECEIVED
            ],
            [
                "label" => "Reclamation rejetée", "value" => self::CLAIM_REJECTED
            ],
            [
                "label" => "Reclamation validée", "value" => self::CLAIM_VALIDATED
            ],
            [
                "label" => "Nouvelle reclamation traitée", "value" => self::CLAIM_TREATED
            ],
            [
                "label" => "Reclamation clôturé", "value" => self::CLAIM_CLOSED
            ],
            [
                "label" => "Reclamation completée", "value" => self::CLAIM_COMPLETED
            ],*/
            [
                "label" => "Nouvelle satisfaction mésurée", "value" => self::SATISFACTION_MEASURED
            ],
        ];
    }

    public static function getEventsValues()
    {
        return [
            //self::CLAIM_REGISTERED,
            //self::CLAIM_RECEIVED,
            //self::CLAIM_REJECTED,
            //self::CLAIM_CLOSED,
            //self::CLAIM_TREATED,
            //self::CLAIM_VALIDATED,
            //self::CLAIM_COMPLETED,
            self::SATISFACTION_MEASURED,
        ];
    }
}