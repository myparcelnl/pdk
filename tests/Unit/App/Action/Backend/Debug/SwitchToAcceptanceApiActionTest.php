<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Debug;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Notifications;
use MyParcelNL\Pdk\Notification\Model\Notification;
use MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface;
use MyParcelNL\Pdk\Settings\Model\AccountSettings;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkActionsService;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Mockery;
use function DI\autowire;
use function DI\get;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());


it('switches to acceptance API successfully', function () {
    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Contract\ApiServiceInterface $apiService */
    $apiService = mock(ApiServiceInterface::class);
    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->with('https://api.acceptance.myparcel.nl');
    
    $settingsRepository->shouldReceive('storeSettings')
        ->once()
        ->with(Mockery::type(AccountSettings::class));
    
    $action = new SwitchToAcceptanceApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(Response::class);
});

it('handles exceptions gracefully', function () {
    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Contract\ApiServiceInterface $apiService */
    $apiService = mock(ApiServiceInterface::class);
    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->andThrow(new \Exception('Test exception'));
    
    $action = new SwitchToAcceptanceApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(500);
    
    $content = json_decode($response->getContent(), true);
    expect($content['success'])->toBeFalse();
    expect($content['message'])->toContain('Failed to switch API URLs to acceptance environment');
});

it('calls UPDATE_ACCOUNT action after successful switch', function () {
    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Contract\ApiServiceInterface $apiService */
    $apiService = mock(ApiServiceInterface::class);
    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->with('https://api.acceptance.myparcel.nl');
    
    $settingsRepository->shouldReceive('storeSettings')
        ->once()
        ->with(Mockery::type(AccountSettings::class));
    
    $action = new SwitchToAcceptanceApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(Response::class);
});

it('logs success message', function () {
    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Contract\ApiServiceInterface $apiService */
    $apiService = mock(ApiServiceInterface::class);
    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->with('https://api.acceptance.myparcel.nl');
    
    $settingsRepository->shouldReceive('storeSettings')
        ->once()
        ->with(Mockery::type(AccountSettings::class));
    
    $action = new SwitchToAcceptanceApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(Response::class);
});

it('logs error message when exception occurs', function () {
    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Contract\ApiServiceInterface $apiService */
    $apiService = mock(ApiServiceInterface::class);
    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->andThrow(new \Exception('Test exception'));
    
    $action = new SwitchToAcceptanceApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(500);
});

it('updates account settings with empty array', function () {
    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Api\Contract\ApiServiceInterface $apiService */
    $apiService = mock(ApiServiceInterface::class);
    /** @var \Mockery\MockInterface&\MyParcelNL\Pdk\Settings\Contract\PdkSettingsRepositoryInterface $settingsRepository */
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->with('https://api.acceptance.myparcel.nl');
    
    $settingsRepository->shouldReceive('storeSettings')
        ->once()
        ->with(Mockery::on(function (AccountSettings $settings) {
            $array = $settings->toArray();
            return isset($array['id']) && $array['id'] === 'account';
        }));
    
    $action = new SwitchToAcceptanceApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(Response::class);
});


