<?php
/** @noinspection StaticClosureCanBeUsedInspection,PhpUnhandledExceptionInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Unit\Fulfilment\Model;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;
use MyParcelNL\Pdk\Tests\Uses\UsesMockPdkInstance;
use function MyParcelNL\Pdk\Tests\usesShared;

usesShared(new UsesMockPdkInstance());

it('creates fulfilment order note from pdk order note', function (array $result, array $input) {
    $pdkOrderNote = OrderNote::fromPdkOrderNote(new PdkOrderNote($input));

    expect($pdkOrderNote->toArrayWithoutNull())->toEqual($result);
})->with('pdkOrderNotesToFulfilmentNotes');
