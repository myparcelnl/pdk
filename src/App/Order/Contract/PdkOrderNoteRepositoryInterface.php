<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Contract;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;

interface PdkOrderNoteRepositoryInterface
{
    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrderNote $note
     *
     * @return void
     */
    public function add(PdkOrderNote $note): void;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection
     */
    public function getFromOrder(PdkOrder $order): PdkOrderNoteCollection;

    /**
     * @param  (string|PdkOrderNote)[] $input
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection
     */
    public function getMany(array $input): PdkOrderNoteCollection;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrderNote $note
     *
     * @return void
     */
    public function update(PdkOrderNote $note): void;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection $notes
     *
     * @return void
     */
    public function updateMany(PdkOrderNoteCollection $notes): void;
}
