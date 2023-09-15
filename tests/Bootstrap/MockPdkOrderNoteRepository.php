<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\App\Order\Collection\PdkOrderNoteCollection;
use MyParcelNL\Pdk\App\Order\Model\PdkOrder;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\App\Order\Repository\AbstractPdkOrderNoteRepository;
use MyParcelNL\Pdk\Storage\Contract\StorageInterface;

class MockPdkOrderNoteRepository extends AbstractPdkOrderNoteRepository
{
    /**
     * @var \MyParcelNL\Pdk\Base\Support\Collection<PdkOrderNoteCollection>
     */
    private readonly PdkOrderNoteCollection $notes;

    /**
     * @param  \MyParcelNL\Pdk\Storage\Contract\StorageInterface $storage
     */
    public function __construct(StorageInterface $storage)
    {
        parent::__construct($storage);

        $this->notes = new PdkOrderNoteCollection();
    }

    public function add(PdkOrderNote $note): void
    {
        $this->update($note);
    }

    public function getFromOrder(PdkOrder $order): PdkOrderNoteCollection
    {
        $notes = $this->notes->get($order->externalIdentifier) ?? [];

        if ($notes instanceof PdkOrderNoteCollection) {
            $notes = $notes->toArray();
        }

        return $this->getMany($notes);
    }

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
