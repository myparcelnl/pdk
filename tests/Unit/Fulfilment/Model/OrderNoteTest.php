<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;

it('creates fulfilment order note from pdk order note', function (array $result, array $input) {
    $pdkOrderNote = OrderNote::fromPdkOrderNote(new PdkOrderNote($input));

    expect($pdkOrderNote->toArrayWithoutNull())->toEqual($result);
})->with('pdkOrderNotesToFulfilmentNotes');
