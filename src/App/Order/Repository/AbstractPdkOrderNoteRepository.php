<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Repository;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Base\Repository\Repository;

abstract class AbstractPdkOrderNoteRepository extends Repository implements PdkOrderNoteRepositoryInterface
{
    public function get($input): PdkOrderNote
    {
        return new PdkOrderNote($input);
    }

    /**
     * @param  (string|PdkOrderNote)[] $input
     */
    public function getMany(array $input): PdkOrderNoteCollection
    {
        return new PdkOrderNoteCollection(array_map($this->get(...), $input));
    }

    public function updateMany(PdkOrderNoteCollection $notes): void
    {
        $notes->each(function (PdkOrderNote $note) {
            $this->update($note);
        });
    }
}
