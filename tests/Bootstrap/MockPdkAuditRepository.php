<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Tests\Bootstrap;

use MyParcelNL\Pdk\Audit\Collection\AuditCollection;
use MyParcelNL\Pdk\Audit\Contract\PdkAuditRepositoryInterface;
use MyParcelNL\Pdk\Audit\Model\Audit;

class MockPdkAuditRepository implements PdkAuditRepositoryInterface
{
    /**
     * @var \MyParcelNL\Pdk\Audit\Collection\AuditCollection
     */
    private $audits;

    public function __construct()
    {
        $this->audits = new AuditCollection();
    }

    /**
     * @return \MyParcelNL\Pdk\Audit\Collection\AuditCollection
     */
    public function all(): AuditCollection
    {
        return $this->audits;
    }

    /**
     * @param  string        $key
     * @param  null|callable $callback
     * @param  bool          $force
     *
     * @return \MyParcelNL\Pdk\Audit\Collection\AuditCollection
     */
    public function retrieve(string $key, ?callable $callback = null, bool $force = false)
    {
        return $this->audits;
    }

    /**
     * @param  string $key
     * @param  mixed  $data
     *
     * @return void
     */
    public function save(string $key, $data): void { }

    /**
     * @param  \MyParcelNL\Pdk\Audit\Model\Audit $audit
     *
     * @return void
     */
    public function store(Audit $audit): void
    {
        $this->audits->push($audit);
    }
}
