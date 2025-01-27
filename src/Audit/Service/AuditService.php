<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Audit\Service;

use MyParcelNL\Pdk\Audit\Collection\AuditCollection;
use MyParcelNL\Pdk\Audit\Contract\AuditServiceInterface;
use MyParcelNL\Pdk\Audit\Contract\PdkAuditRepositoryInterface;
use MyParcelNL\Pdk\Audit\Model\Audit;
use MyParcelNL\Pdk\Base\Model\Model;

class AuditService implements AuditServiceInterface
{
    /**
     * @var \MyParcelNL\Pdk\Audit\Contract\PdkAuditRepositoryInterface
     */
    private $auditRepository;

    /**
     * @param  \MyParcelNL\Pdk\Audit\Contract\PdkAuditRepositoryInterface $auditRepository
     *
     * @deprecated Audits functionality will be removed in the next major release
     */
    public function __construct(PdkAuditRepositoryInterface $auditRepository)
    {
        $this->auditRepository = $auditRepository;
    }

    /**
     * @param  \MyParcelNL\Pdk\Audit\Model\Audit $audit
     *
     * @return \MyParcelNL\Pdk\Audit\Model\Audit
     * @deprecated Audits functionality will be removed in the next major release
     */
    public function add(Audit $audit): Audit
    {
        $this->auditRepository->store($audit);

        return $audit;
    }

    /**
     * @return \MyParcelNL\Pdk\Audit\Collection\AuditCollection
     * @deprecated Audits functionality will be removed in the next major release
     */
    public function all(): AuditCollection
    {
        return $this->auditRepository->all();
    }

    /**
     * @param  class-string<Model> $model
     * @param  string              $identifier
     *
     * @return \MyParcelNL\Pdk\Audit\Collection\AuditCollection
     * @deprecated Audits functionality will be removed in the next major release
     */
    public function allByModel(string $model, string $identifier): AuditCollection
    {
        return $this->all()
            ->where('model', $model)
            ->where('modelIdentifier', $identifier);
    }
}
