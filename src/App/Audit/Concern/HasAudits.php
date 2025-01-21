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
     * @deprecated only used in test now
     * @return void
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
     */
    protected function getAuditsAttribute(): AuditCollection
    {
        $identifier = $this->getAttribute($this->auditIdentifier);

        return $identifier
            ? Audits::allByModel(static::class, $identifier)
            : new AuditCollection();
    }

    /**
     * @return void
     */
    protected function initializeHasAudits(): void
    {
        if (null === $this->auditIdentifier) {
            throw new InvalidArgumentException('Audit identifier is not set');
        }
    }
}
