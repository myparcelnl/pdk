<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Action\Addresses;

use Symfony\Component\HttpFoundation\Request;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use MyParcelNL\Pdk\App\Action\Addresses\AddressesValidateAction;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Mockery;
use MyParcelNL\Pdk\App\Action\Addresses\AddressesListAction;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('correctly builds the proxy request', function () {
    // Ensure required parameters are present with correct format
    $queryParams = [
        'countryCode'       => 'NL',
        'postalCode'        => '1234AB',
        'houseNumber'       => '1',
        // These are not valid, verify they are not sent.
        'city'              => 'Amsterdam',
        'region'            => 'North-Holland',
        'street'            => 'Damstraat',
    ];
    $incomingRequest = new Request($queryParams);

    $mockService = Mockery::mock(AddressesApiService::class);

    $action = new AddressesListAction($mockService);
    // Verify the request was built correctly
    $request = $action->buildRequest($incomingRequest);
    expect($request->getQueryString())->toBe('countryCode=NL&postalCode=1234AB&houseNumber=1&limit=5');
});
