<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\App\Action\Addresses;

use Symfony\Component\HttpFoundation\Request;
use MyParcelNL\Pdk\Api\Service\AddressesApiService;
use MyParcelNL\Pdk\App\Action\Addresses\AddressesValidateAction;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Mockery;

use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('correctly builds the proxy request', function () {
    // Ensure required parameters are present with correct format
    $queryParams = [
        'countryCode'       => 'NL', // Mock country code for the Netherlands
        'postalCode'        => '1234AB', // Mock postal code
        'houseNumber'       => '1', // Mock house number
        'houseNumberSuffix' => 'A', // Mock house number suffix
        'city'              => 'Amsterdam', // Mock city
        'region'            => 'North-Holland', // Mock region
        'street'            => 'Damstraat', // Mock street
        'validationType'    => 'full', // Mock validation type
    ];
    $incomingRequest = new Request($queryParams);

    $mockService = Mockery::mock(AddressesApiService::class);

    $action = new AddressesValidateAction($mockService);
    // Verify the request was built correctly
    $request = $action->buildRequest($incomingRequest);
    expect($request->getQueryString())->toBe('postalCode=1234AB&houseNumber=1&houseNumberSuffix=A&city=Amsterdam&region=North-Holland&street=Damstraat&validationType=full');
});
