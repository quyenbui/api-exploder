<?php

return call_user_func(function () {
    return [
        'debug' => true,
        'services' => [
            'account' => [
                'resource_definition' => 'http://localhost:3000/resources',
                'service_address' => 'http://localhost:3000/'
            ]
        ],
        'cache.options' => [
            'driver' => 'files',
            'path' => '/tmp/api-exploder/cache'
        ],
    ];
});
