<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderNoteRepository;
use MyParcelNL\Pdk\Base\Support\Arr;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

class MockPdkOrderNoteRepository extends AbstractPdkOrderNoteRepository
{
    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection<PdkOrderNoteCollection>
     */
    private $notes;

    /**
     * @param  array                                             $orderNotes
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface $storage
     *
     * @noinspection PhpOptionalBeforeRequiredParametersInspection
     */
    public function __construct($orderNotes = [], StorageInterface $storage)
    {
        parent::__construct($storage);

        $this->notes = new PdkOrderNoteCollection(Arr::wrap($orderNotes));
    }

    public function add(PdkOrderNote $note): void
    {
        $this->update($note);
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Model\PdkOrder $order
     *
     * @return \MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection
     */
    public function getFromOrder(PdkOrder $order): PdkOrderNoteCollection
    {
        $notes = $this->notes->get($order->externalIdentifier) ?? [];

        if ($notes instanceof PdkOrderNoteCollection) {
            $notes = $notes->toArray();
        }

        return $this->getMany($notes);
    }

    /**
     * @param  PdkOrderNote $note
     *
     * @return void
     */
    public function update(PdkOrderNote $note): void
    {
        if (! $this->notes->has($note->orderIdentifier)) {
            $this->notes->put($note->orderIdentifier, new PdkOrderNoteCollection());
        }

        $this->notes
            ->get($note->orderIdentifier)
            ->push($note);
    }
}
