<?php
/** @noinspection PhpUnhandledExceptionInspection,StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;

it('creates pdk order note from fulfilment note', function (array $input, array $result) {
    $fulfilmentNote = PdkOrderNote::fromFulfilmentOrderNote(new OrderNote($input));

    expect($fulfilmentNote->toArrayWithoutNull())->toEqual($result);
})->with('pdkOrderNotesToFulfilmentNotes');
