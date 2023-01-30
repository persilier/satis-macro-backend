<?php
return [
    "table" => "claims", // Represents the name of the table in which you want to save the different received mails
    "message_format" => "html_text", // The format in which you want to save the messages received from the mail :
    // html_text => to save the message in html format and plain_text for plain text
    "object" => [ // The different fields that will be taken into account to record in the table
        "first_name" => "firstname", // Customize the name of the first name field of the sender
        "last_name" => "lastname", // Customize the name of the sender "s name field
        "email" => "email", // Customize the name of the email field
        "message" => "description", // Customize the field name of the message to be saved
        "date" => "created_at", // Customize the field name of the date the mail was sent
        "attachments" => "attachments", // Customize the name of the file field, this field is of type json
    ],
    "app_registration" => [ //Incoming mail service subscription information
        "app_name" => 'Care - Rwanda', // Name of your application,
        "url" => 'http://localhost:8000/api/incoming/mail', // Registration link for incoming mail
        "mail_server" => 'smtp.gmail.com', // Mail host
        "mail_server_username" => 'test@gmail.com', // Mail username
        "mail_server_password" => '****', // Mail password
        "mail_server_port" => '587', // Mail port
        "app_login_url" => 'http://localhost:8000/api/auth', // Api authentication
        "app_login_params" => [ // Information to connect to your project. The different colunms to change following
            // arguments needed to connect to your project
            "grant_type" => "password",
            "client_id" => "*****************",
            "client_secret" => "*****************************",
            "username" => "user@example.com",
            "password" => "******"
        ],
        "incoming_mail_service_subscribe"=>"http://incoming-mail-service.local/api/subscribe"
    ]
];
