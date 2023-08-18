<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Mock\Model\MockCastModel;
use Psr\Log\LoggerInterface;

it('warns on use of deprecated attributes and sets value in correct property', function () {
    $model = new MockCastModel([
        'broccoli' => 'nice',
    ]);

    /** @var \MyParcelNL\Pdk\Logger\MockLogger $logger */
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

