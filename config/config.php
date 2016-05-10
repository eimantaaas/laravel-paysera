<?php
return [
    'projectid'     => '',
    'sign_password' => '',
    'currency'      => 'EUR',
    'country'       => 'LT',
    // Test mode (sand box) 0 - off or 1 - on
    'test'          => 1,

    'statuses'      => [
        '0' => 'payment_declined',
        '1' => 'payment_approved',
        '2' => 'payment_pending'
    ],

    // Route names for callbacks
    'accepturl'     => '',
    'cancelurl'     => '',
    'callbackurl'   => ''

];