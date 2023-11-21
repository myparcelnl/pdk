<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Audit\Concern;

use DateTime;
use InvalidArgumentException;
use MyParcelNL\Pdk\Audit\Collection\AuditCollection;
use MyParcelNL\Pdk\Audit\Model\Audit;
use MyParcelNL\Pdk\Facade\Audits;

/**
 * @property AuditCollection $audits
 */
trait HasAudits
{
    /**
     * @param  string      $action
     * @param  null|string $type
     * @param  null|array  $arguments
     *
     * @return void
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function addAudit(string $action, ?string $type = null, ?array $arguments = []): void
    {
        $id    = uniqid('', true);
        $audit = new Audit([
            'id'              => $id,
            'arguments'       => $arguments,
            'action'          => $action,
            'model'           => static::class,
            'modelIdentifier' => $this->getAttribute($this->auditIdentifier),
            'created'         => new DateTime(),
            'type'            => $type,
        ]);

        Audits::add($audit);
    }

    /**
     * @return \MyParcelNL\Pdk\Audit\Collection\AuditCollection
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    protected function getAuditsAttribute(): AuditCollection
    {
        return Audits::allByModel(static::class, $this->getAttribute($this->auditIdentifier));
    }

    /**
     * @return void
     */
    protected function initializeHasAudits(): void
    {
        if (null === $this->auditIdentifier) {
            throw new InvalidArgumentException('Audit identifier is not set');
        }

        $this->attributes['audits'] = null;
        $this->casts['audits']      = AuditCollection::class;
    }
}
