<?php
/** @noinspection StaticClosureCanBeUsedInspection */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use MyParcelNL\Pdk\Console\Command\GenerateMigrationCommand;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Overrides date() for GenerateMigrationCommand, which calls it unqualified in this
 * namespace, so the generated timestamp is deterministic when a test pins it. Falls back
 * to the real date() when $GLOBALS['__fake_now'] is not set.
 */
function date(string $format): string
{
    return $GLOBALS['__fake_now'] ?? \date($format);
}

it('generates a timestamped migration file in the default src/Migration dir', function () {
    $tmpRoot   = sys_get_temp_dir() . '/pdk_make_migration_' . uniqid('', true);
    $targetDir = $tmpRoot . '/src/Migration';
    mkdir($targetDir, 0777, true);

    $prevCwd = getcwd();
    chdir($tmpRoot);

    try {
        $command = new GenerateMigrationCommand();
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

it('respects the --dir option', function () {
    $tmpRoot   = sys_get_temp_dir() . '/pdk_make_migration_' . uniqid('', true);
    $targetDir = $tmpRoot . '/src/CustomMigrations';
    mkdir($targetDir, 0777, true);

    $prevCwd = getcwd();
    chdir($tmpRoot);

    try {
        $command = new GenerateMigrationCommand();
        $tester  = new CommandTester($command);
        $tester->execute([
            'slug'           => 'foo_bar',
            '--dir' => 'src/CustomMigrations',
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
        $command = new GenerateMigrationCommand();
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

    // Pin the timestamp so both runs target the same filename — no same-second race.
    $GLOBALS['__fake_now'] = '2026_01_01_000000';

    try {
        $command = new GenerateMigrationCommand();

        // First run creates the file.
        $tester = new CommandTester($command);
        $tester->execute(['slug' => 'ok_slug']);
        expect($tester->getStatusCode())->toBe(0);

        $path = $targetDir . '/2026_01_01_000000_ok_slug.php';
        expect(file_exists($path))->toBeTrue();

        // Mark the content so a clobber would be detectable.
        file_put_contents($path, '<?php // sentinel');

        // Second run targets the same filename and must be rejected.
        $tester2 = new CommandTester($command);
        $tester2->execute(['slug' => 'ok_slug']);
        expect($tester2->getStatusCode())->not->toBe(0);

        // Original content must be preserved.
        expect(file_get_contents($path))->toBe('<?php // sentinel');
    } finally {
        unset($GLOBALS['__fake_now']);
        chdir($prevCwd);
        array_map('unlink', glob($targetDir . '/*.php') ?: []);
        @rmdir($targetDir);
        @rmdir($tmpRoot . '/src');
        @rmdir($tmpRoot);
    }
});
