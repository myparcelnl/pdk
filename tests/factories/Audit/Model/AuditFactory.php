<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Audit\Model;

use DateTime;
use MyParcelNL\Pdk\Audit\Contract\PdkAuditRepositoryInterface;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of Audit
 * @method Audit make()
 * @method $this withArguments(array $arguments)
 * @method $this withId(string $id)
 * @method $this withType(string $type)
 * @method $this withAction(string $action)
 * @method $this withModel(string $model)
 * @method $this withModelIdentifier(string $modelIdentifier)
 * @method $this withCreated(DateTime $created)
 */
final class AuditFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return Audit::class;
    }

    /**
     * @param  T $model
     *
     * @return void
     */
    protected function save(Model $model): void
    {
        /** @var \MyParcelNL\Pdk\Audit\Contract\PdkAuditRepositoryInterface $repository */
        $repository = Pdk::get(PdkAuditRepositoryInterface::class);

        $repository->store($model);
    }
}
