<?php

return [

    /*
    |--------------------------------------------------------------------------
    | oAuth Token
    |--------------------------------------------------------------------------
    |
    | Some API calls need to be authenticated via an oAuth token.
    | Get yours at https://github.com/settings/tokens
    |
    */

    'oAuthToken' => '',

    /*
    |--------------------------------------------------------------------------
    | User Agent
    |--------------------------------------------------------------------------
    |
    | Github requires that all API calls are sent with user-agent header and requests that you use your Github username.
    | If no userAgent is given, "laravel-github" will be used as a default value which is quite undesirable.
    |
    */

    'userAgent' => '',

];
