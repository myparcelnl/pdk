<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\Base\Factory\PdkFactory;
use MyParcelNL\Pdk\Base\Pdk;
use MyParcelNL\Pdk\Base\PdkActions;
use MyParcelNL\Pdk\Plugin\Action\PdkActionManager;
use MyParcelNL\Pdk\Tests\Bootstrap\MockPdkConfig;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use Symfony\Component\HttpFoundation\Response;
use function DI\value;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('returns error response on nonexistent action', function () {
    /** @var \MyParcelNL\Pdk\Plugin\Action\PdkActionManager $manager */
    $manager  = \MyParcelNL\Pdk\Facade\Pdk::get(PdkActionManager::class);
    $response = $manager->execute(['action' => 'nonexistent']);

    if (! $response) {
        throw new RuntimeException('Response is not set');
    }

    expect($response->getStatusCode())
        ->toBe(Response::HTTP_BAD_REQUEST)
        ->and(json_decode($response->getContent(), true))
        ->toBe([
            'message'    => 'error :(',
            'request_id' => '',
            'errors'     => [
                [
                    'status'  => 500,
                    'code'    => 422,
                    'title'   => 'Action "nonexistent" does not exist.',
                    'message' => 'Action "nonexistent" does not exist.',
                    'trace'   => 'Enable development mode to see stack trace.',
                ],
            ],
        ]);
});

it('shows stack trace in development mode', function () {
    PdkFactory::create(MockPdkConfig::create(['mode' => value(Pdk::MODE_DEVELOPMENT)]));

    /** @var \MyParcelNL\Pdk\Plugin\Action\PdkActionManager $manager */
    $manager  = \MyParcelNL\Pdk\Facade\Pdk::get(PdkActionManager::class);
    $response = $manager->execute(['action' => 'nonexistent']);

    if (! $response) {
        throw new RuntimeException('Response is not set');
    }

    expect($response->getStatusCode())
        ->toBe(Response::HTTP_BAD_REQUEST)
        ->and(json_decode($response->getContent(), true)['errors'][0]['trace'])
        ->toBeArray();
});

it('ignores missing action if it is optional', function () {
    /** @var \MyParcelNL\Pdk\Plugin\Action\PdkActionManager $manager */
    $manager  = \MyParcelNL\Pdk\Facade\Pdk::get(PdkActionManager::class);
    $response = $manager->execute(['action' => PdkActions::UPDATE_TRACKING_NUMBER]);

    expect($response)
        ->toBe(null);
});
