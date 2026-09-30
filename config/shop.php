<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Delivery origin
    |--------------------------------------------------------------------------
    |
    | Centre of the commune of Dammartin-en-Goële (INSEE 77153) as published
    | by geo.api.gouv.fr. Delivery distances are measured as the crow flies
    | from this point (KTD6). It is the commune centre, not the pickup address.
    |
    */

    'origin' => [
        'latitude' => 49.0504,
        'longitude' => 2.6777,
    ],

    /*
    | Departments served: Île-de-France and Oise (R16).
    */

    'departments' => ['75', '77', '78', '91', '92', '93', '94', '95', '60'],

];
