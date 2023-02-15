<?php

return [
    "api_subscriber" => env('API_SUBSCRIPTION_INCOMING_EMAIL'),
    "grant_type" => env('EMAIL_CLAIM_CONFIGURATION_GRANT_TYPE'),
    "client_id" => env('EMAIL_CLAIM_CONFIGURATION_CLIENT_ID'),
    "client_secret" => env('EMAIL_CLAIM_CONFIGURATION_CLIENT_SECRET'),
    "app_url_incoming_mail" => env('APP_URL_INCOMING_MAIL', NULL),
    "claim_object_prediction" => env('CLAIM_OBJECT_PREDICTION'),
    "claim_unit_prediction" => env('CLAIM_UNIT_PREDICTION'),
    "scan_file_claim_prediction" => env('SCAN_FILE_CLAIM_PREDICTION'),

];