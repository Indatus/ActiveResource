<?php return array(


    /*
    |--------------------------------------------------------------------------
    | API endpoint URI
    |--------------------------------------------------------------------------
    |
    | This is the base URI that your REST API requests will be made to.
    | It should be in a format such as http://my-endpoint.com
    |
    */

    'base_uri' => null,

    /*
    |--------------------------------------------------------------------------
    | HTTP method request parameter
    |--------------------------------------------------------------------------
    |
    | This is a parameter to send with the request that will contain 
    | a string disclosing the desired HTTP method ('put', 'post', 'patch', 
    | 'delete').  If specified PUT, POST, PATCH and DELETE requests will 
    | all be made as a POST and the given parameter will be added
    | with the http method as it's value. An example might be "_method".
    | 
    | Otherwise a true PUT, POST, PATCH or DELETE request will be made
    |
    */

    'http_method_param' => '',

    /*
    |--------------------------------------------------------------------------
    | Scratch Disk location
    |--------------------------------------------------------------------------
    |
    | This is a filesystem path where temporary files could be written if needed.
    |
    | An example would be an Entity attribute that is a file (via base64 encoded 
    | string).  The file would be written to the scratch disk before sending to 
    | the endpoint, then sent with the request via HTTP chunked transfer encoding.
    |
    */

    'scratch_disk_location' => '/tmp',

);
