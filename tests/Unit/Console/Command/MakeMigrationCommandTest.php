<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use MyParcelNL\Pdk\Console\Command\MakeMigrationCommand;
use Symfony\Component\Console\Tester\CommandTester;

it('generates a timestamped migration file in the default src/Migration dir', function () {
    $tmpRoot   = sys_get_temp_dir() . '/pdk_make_migration_' . uniqid('', true);
    $targetDir = $tmpRoot . '/src/Migration';
    mkdir($targetDir, 0777, true);

    $prevCwd = getcwd();
    chdir($tmpRoot);

    try {
        $command = new MakeMigrationCommand();
        $tester  = new CommandTester($command);
        $tester->execute(['slug' => 'migrate_carriers_to_v2']);

        expect($tester->getStatusCode())->toBe(0);

        $files = glob($targetDir . '/*_migrate_carriers_to_v2.php');
        expect($files)->toHaveCount(1);

        $basename = pathinfo($files[0], PATHINFO_FILENAME);
        expect($basename)->toMatch('/^\d{4}_\d{2}_\d{2}_\d{6}_migrate_carriers_to_v2$/');

        $contents = file_get_contents($files[0]);
        expect($contents)
            ->toContain('extends AbstractTimestampedMigration')
            ->toContain('public function up(): void')
            ->toContain('public function down(): void');
    } finally {
        chdir($prevCwd);
        array_map('unlink', glob($targetDir . '/*.php') ?: []);
        @rmdir($targetDir);
        @rmdir($tmpRoot . '/src');
        @rmdir($tmpRoot);
    }
});

it('respects the --upgrade-path option', function () {
    $tmpRoot   = sys_get_temp_dir() . '/pdk_make_migration_' . uniqid('', true);
    $targetDir = $tmpRoot . '/src/CustomMigrations';
    mkdir($targetDir, 0777, true);

    $prevCwd = getcwd();
    chdir($tmpRoot);

    try {
        $command = new MakeMigrationCommand();
        $tester  = new CommandTester($command);
        $tester->execute([
            'slug'           => 'foo_bar',
            '--upgrade-path' => 'src/CustomMigrations',
        ]);

        expect($tester->getStatusCode())->toBe(0);

        $files = glob($targetDir . '/*_foo_bar.php');
        expect($files)->toHaveCount(1);
    } finally {
        chdir($prevCwd);
        array_map('unlink', glob($targetDir . '/*.php') ?: []);
        @rmdir($targetDir);
        @rmdir($tmpRoot . '/src');
        @rmdir($tmpRoot);
    }
});

it('rejects an invalid slug and writes no file', function () {
    $tmpRoot   = sys_get_temp_dir() . '/pdk_make_migration_' . uniqid('', true);
    $targetDir = $tmpRoot . '/src/Migration';
    mkdir($targetDir, 0777, true);

    $prevCwd = getcwd();
    chdir($tmpRoot);

    try {
        $command = new MakeMigrationCommand();
        $tester  = new CommandTester($command);
        $tester->execute(['slug' => 'Bad-Slug']);

        expect($tester->getStatusCode())->not->toBe(0);

        $files = glob($targetDir . '/*.php');
        expect($files)->toHaveCount(0);
    } finally {
        chdir($prevCwd);
        array_map('unlink', glob($targetDir . '/*.php') ?: []);
        @rmdir($targetDir);
        @rmdir($tmpRoot . '/src');
        @rmdir($tmpRoot);
    }
});

it('refuses to overwrite an existing migration file', function () {
    $tmpRoot   = sys_get_temp_dir() . '/pdk_make_migration_' . uniqid('', true);
    $targetDir = $tmpRoot . '/src/Migration';
    mkdir($targetDir, 0777, true);

    $prevCwd = getcwd();
    chdir($tmpRoot);

    try {
        $command = new MakeMigrationCommand();

        // First run: file is created successfully.
        $tester = new CommandTester($command);
        $tester->execute(['slug' => 'ok_slug']);
        expect($tester->getStatusCode())->toBe(0);

        $files = glob($targetDir . '/*_ok_slug.php');
        expect($files)->toHaveCount(1);

        // Replace the file content so we can detect if it gets clobbered.
        $existingPath = $files[0];
        file_put_contents($existingPath, '<?php // sentinel');

        // Rename the file so its timestamp matches "now" exactly — forcing a collision.
        $collisionPath = $targetDir . '/' . date('Y_m_d_His') . '_ok_slug.php';
        rename($existingPath, $collisionPath);

        // Second run within the same second must be rejected.
        $tester2 = new CommandTester($command);
        $tester2->execute(['slug' => 'ok_slug']);
        expect($tester2->getStatusCode())->not->toBe(0);

        // Original content must be preserved.
        expect(file_get_contents($collisionPath))->toBe('<?php // sentinel');
    } finally {
        chdir($prevCwd);
        array_map('unlink', glob($targetDir . '/*.php') ?: []);
        @rmdir($targetDir);
        @rmdir($tmpRoot . '/src');
        @rmdir($tmpRoot);
    }
});
