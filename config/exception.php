<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Dont report list
    |--------------------------------------------------------------------------
    |
    | A list of the exception types that are not reported.
    |
    */

    'dont_report' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Conversion to ServiceException
    |--------------------------------------------------------------------------
    |
    | Convert the current prompt to throw the specified exception
    | If the index of the array is a number, it is automatically converted to a ServiceException
    | Example:
    | ResourceNotFoundException::class => CrCms\Microservice\Foundation\Exceptions\NotFoundException::class
    | ResourceAddErrorException::class
    |
    */
    'conversion' => [

    ],

];