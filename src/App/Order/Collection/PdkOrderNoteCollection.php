<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;

class PdkOrderNoteCollection extends Collection
{
    protected $cast = PdkOrderNote::class;

    /**
     * @return \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection
     */
    public function toFulfilmentCollection(): OrderNoteCollection
    {
        $fulfilmentNotes = $this
            ->map(function (PdkOrderNote $pdkOrderNote) {
                return OrderNote::fromPdkOrderNote($pdkOrderNote);
            })
            ->all();

        return new OrderNoteCollection($fulfilmentNotes);
    }
}
