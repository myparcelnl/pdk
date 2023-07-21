<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Mocks\MockCastModel;
use MyParcelNL\Pdk\Tests\Uses\UsesEachMockPdkInstance;
use Psr\Log\LoggerInterface;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesEachMockPdkInstance());

it('warns on use of deprecated attributes and sets value in correct property', function () {
    $model = new MockCastModel([
        'broccoli' => 'nice',
    ]);

    /** @var \MyParcelNL\Pdk\Tests\Bootstrap\MockLogger $logger */
    $logger = Pdk::get(LoggerInterface::class);

    expect($model->property)
        ->toEqual('nice')
        ->and($model->broccoli)
        ->toBeNull()
        ->and($logger->getLogs())
        ->toEqual([
            [
                'level'   => 'warning',
                'message' => "[PDK]: [DEPRECATION] Attribute 'broccoli' is deprecated. Use 'property' instead.",
                'context' => [
                    'class' => MockCastModel::class,
                ],
            ],
        ]);
});

