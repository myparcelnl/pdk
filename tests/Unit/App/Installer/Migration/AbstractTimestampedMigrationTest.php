<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Installer\Migration;

use LogicException;

/**
 * @return \MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration
 */
function makeTimestampedMigration(): AbstractTimestampedMigration
{
    return new class extends AbstractTimestampedMigration {
        public function up(): void
        {
            // no-op
        }
    };
}

it('throws when getId() is called before an identity is set', function () {
    makeTimestampedMigration()->getId();
})->throws(LogicException::class, 'Migration identity has not been set');

it('returns the injected identity from getId()', function () {
    $migration = makeTimestampedMigration();
    $migration->setIdentity('2026_01_01_000000_example');

    expect($migration->getId())->toBe('2026_01_01_000000_example');
});

it('throws on getVersion() because timestamp migrations are not version-gated', function () {
    makeTimestampedMigration()->getVersion();
})->throws(LogicException::class, 'not version-gated');

it('has a no-op down() by default', function () {
    $migration = makeTimestampedMigration();
    $migration->setIdentity('2026_01_01_000000_example');

    $migration->down();

    // Nothing to assert beyond it running without error: the default down() is a no-op.
    expect($migration->getId())->toBe('2026_01_01_000000_example');
});
