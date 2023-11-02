<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Collection;

use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Base\Support\Collection;

/**
 * @property PdkOrderNote[] $items
 */
class PdkOrderNoteCollection extends Collection
{
    protected $cast = PdkOrderNote::class;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection $notes
     *
     * @return void
     */
    public function addApiIdentifiers(PdkOrderNoteCollection $notes): void
    {
        $this->each(function (PdkOrderNote $note, $index) use ($notes) {
            if (! $notes->offsetExists($index)) {
                return;
            }

            /** @var PdkOrderNote $apiNote */
            $apiNote = $notes->offsetGet($index);

            $note->apiIdentifier = $apiNote->uuid ?? null;
        });
    }
}
