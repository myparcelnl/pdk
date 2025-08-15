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

beforeEach(function () {
    // Create a cache file to simulate acceptance environment
    $cacheFile = sys_get_temp_dir() . '/pdk_acceptance_api_url.txt';
    file_put_contents($cacheFile, 'https://api.acceptance.myparcel.nl');
});

afterEach(function () {
    // Clean up cache file after each test
    $cacheFile = sys_get_temp_dir() . '/pdk_acceptance_api_url.txt';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
});

it('switches to production API successfully', function () {
    $apiService = mock(ApiServiceInterface::class);
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->with('https://api.myparcel.nl');
    
    $settingsRepository->shouldReceive('storeSettings')
        ->once()
        ->with(Mockery::type(AccountSettings::class));
    
    $action = new SwitchToProductionApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(Response::class);
    
    // Check if cache file was removed
    $cacheFile = sys_get_temp_dir() . '/pdk_acceptance_api_url.txt';
    expect(file_exists($cacheFile))->toBeFalse();
});

it('handles exceptions gracefully', function () {
    $apiService = mock(ApiServiceInterface::class);
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->andThrow(new \Exception('Test exception'));
    
    $action = new SwitchToProductionApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(500);
    
    $content = json_decode($response->getContent(), true);
    expect($content['success'])->toBeFalse();
    expect($content['message'])->toContain('Failed to switch API URL back to production environment');
});

it('calls UPDATE_ACCOUNT action after successful switch', function () {
    $apiService = mock(ApiServiceInterface::class);
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->with('https://api.myparcel.nl');
    
    $settingsRepository->shouldReceive('storeSettings')
        ->once()
        ->with(Mockery::type(AccountSettings::class));
    
    $action = new SwitchToProductionApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(Response::class);
});

it('logs success message', function () {
    $apiService = mock(ApiServiceInterface::class);
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->with('https://api.myparcel.nl');
    
    $settingsRepository->shouldReceive('storeSettings')
        ->once()
        ->with(Mockery::type(AccountSettings::class));
    
    $action = new SwitchToProductionApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(Response::class);
});

it('logs error message when exception occurs', function () {
    $apiService = mock(ApiServiceInterface::class);
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->andThrow(new \Exception('Test exception'));
    
    $action = new SwitchToProductionApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(500);
});

it('updates account settings with empty array', function () {
    $apiService = mock(ApiServiceInterface::class);
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->with('https://api.myparcel.nl');
    
    $settingsRepository->shouldReceive('storeSettings')
        ->once()
        ->with(Mockery::on(function (AccountSettings $settings) {
            $array = $settings->toArray();
            return isset($array['id']) && $array['id'] === 'account';
        }));
    
    $action = new SwitchToProductionApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(Response::class);
});

it('removes cache file even if it does not exist', function () {
    // Remove cache file first
    $cacheFile = sys_get_temp_dir() . '/pdk_acceptance_api_url.txt';
    if (file_exists($cacheFile)) {
        unlink($cacheFile);
    }
    
    $apiService = mock(ApiServiceInterface::class);
    $settingsRepository = mock(PdkSettingsRepositoryInterface::class);
    
    $apiService->shouldReceive('setBaseUrl')
        ->once()
        ->with('https://api.myparcel.nl');
    
    $settingsRepository->shouldReceive('storeSettings')
        ->once()
        ->with(Mockery::type(AccountSettings::class));
    
    $action = new SwitchToProductionApiAction($apiService, $settingsRepository);
    $request = new Request();
    
    $response = $action->handle($request);
    
    expect($response)->toBeInstanceOf(Response::class);
    expect(file_exists($cacheFile))->toBeFalse();
});


