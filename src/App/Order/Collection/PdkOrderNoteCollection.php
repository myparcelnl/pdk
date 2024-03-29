<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Base\Support\Collection;
use MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection;
use MyParcelNL\Pdk\Fulfilment\Model\OrderNote;

/**
 * @property PdkOrderNote[] $items
 */
class PdkOrderNoteCollection extends Collection
{
    protected $cast = PdkOrderNote::class;

    /**
     * @param  \MyParcelNL\Pdk\Fulfilment\Collection\OrderNoteCollection $notes
     *
     * @return void
     */
    public function addApiIdentifiers(OrderNoteCollection $notes): void
    {
        $this->each(function (PdkOrderNote $note, $index) use ($notes) {
            if (! $notes->offsetExists($index)) {
                return;
            }

            /** @var OrderNote $apiNote */
            $apiNote = $notes->offsetGet($index);

            $note->apiIdentifier = $apiNote->uuid ?? null;
        });
    }

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
