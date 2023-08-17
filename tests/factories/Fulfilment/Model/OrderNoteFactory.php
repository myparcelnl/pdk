<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Fulfilment\Model;

use DateTime;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of OrderNote
 * @method OrderNote make()
 * @method $this withAuthor(string $author)
 * @method $this withCreatedAt(string|DateTime $createdAt)
 * @method $this withNote(string $note)
 * @method $this withUpdatedAt(string|DateTime $updatedAt)
 * @method $this withUuid(string $uuid)
 */
final class OrderNoteFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return OrderNote::class;
    }
}
