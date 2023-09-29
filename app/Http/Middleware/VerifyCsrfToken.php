<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        //
        'http://localhost:8080/frtplus/public/getEligibility',
        'http://localhost:8080/frtplus/public/createShipment',
        'https://frtplus.tatara.co.uk/public/getEligibility',
        'https://frtplus.tatara.co.uk/public/createShipment'
    ];
}
