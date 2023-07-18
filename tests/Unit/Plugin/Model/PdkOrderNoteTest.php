<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('creates pdk order note from fulfilment note', function (array $input, array $result) {
    $fulfilmentNote = PdkOrderNote::fromFulfilmentOrderNote(new OrderNote($input));

    expect($fulfilmentNote->toArrayWithoutNull())->toEqual($result);
})->with('pdkOrderNotesToFulfilmentNotes');
