<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Order\Model;

use DateTime;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Contract\FactoryInterface;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of PdkOrderNote
 * @method PdkOrderNote make()
 * @method $this withApiIdentifier(null|string $apiIdentifier)
 * @method $this withAuthor(string $author)
 * @method $this withCreatedAt(string|DateTime $createdAt)
 * @method $this withExternalIdentifier(null|string $externalIdentifier)
 * @method $this withNote(string $note)
 * @method $this withOrderIdentifier(null|string $orderIdentifier)
 * @method $this withUpdatedAt(string|DateTime $updatedAt)
 */
final class PdkOrderNoteFactory extends AbstractModelFactory
{
    public function byCustomer(): self
    {
        return $this->withAuthor(PdkOrderNote::AUTHOR_CUSTOMER);
    }

    public function byWebshop(): self
    {
        return $this->withAuthor(PdkOrderNote::AUTHOR_WEBSHOP);
    }

    public function getModel(): string
    {
        return PdkOrderNote::class;
    }

    protected function createDefault(): FactoryInterface
    {
        return $this
            ->byWebshop()
            ->withNote('test note')
            ->withCreatedAt(new DateTime('2023-01-01 12:00:00'))
            ->withUpdatedAt(new DateTime('2023-01-01 12:00:00'));
    }

    /**
     * @param  T $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        $repository = Pdk::get(PdkOrderNoteRepositoryInterface::class);

        $repository->add($model);
    }
}
