<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Contract;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;

interface PdkOrderNoteRepositoryInterface
{
    public function add(PdkOrderNote $note): void;

    /**
     * @return mixed
     */
    public function getFromOrder(PdkOrder $order): PdkOrderNoteCollection;

    /**
     * @param  (string|PdkOrderNote)[] $input
     */
    public function getMany(array $input): PdkOrderNoteCollection;

    public function update(PdkOrderNote $note): void;

    public function updateMany(PdkOrderNoteCollection $note): void;
}
