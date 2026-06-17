<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Console\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class MakeMigrationCommand extends Command
{
    protected static $defaultName = 'make:migration';

    private const DEFAULT_UPGRADE_PATH = 'src/Migration';

    protected function configure(): void
    {
        $this
            ->setDescription('Generate a timestamped migration stub file.')
            ->addArgument(
                'slug',
                InputArgument::REQUIRED,
                'Migration slug in snake_case, e.g. "migrate_carriers_to_v2"'
            )
            ->addOption(
                'upgrade-path',
                null,
                InputOption::VALUE_REQUIRED,
                'Target directory relative to the current working directory.',
                self::DEFAULT_UPGRADE_PATH
            );
    }

    /**
     * Generates a timestamped migration stub file in the target directory.
     *
     * Returns 0 when the file is written, or 1 when the slug is invalid, the target
     * directory does not exist, or the file already exists. Plain integers are used
     * instead of the Command::SUCCESS/FAILURE constants, which only exist in
     * symfony/console 4.4+ (this command must also run on the supported 2.x/3.x).
     *
     * @noinspection ReturnTypeCanBeDeclaredInspection
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $slug = (string) $input->getArgument('slug');

        if (! preg_match('/^[a-z][a-z0-9_]{0,79}$/', $slug)) {
            $output->writeln(sprintf(
                '<error>Invalid slug "%s". Must match ^[a-z][a-z0-9_]{0,79}$.</error>',
                $slug
            ));

            return 1;
        }

        $cwd = getcwd();

        if (false === $cwd) {
            $output->writeln('<error>Unable to determine the current working directory.</error>');

            return 1;
        }

        $upgradePath = trim((string) $input->getOption('upgrade-path'), '/');
        $targetDir   = rtrim($cwd, '/') . '/' . $upgradePath;

        if (! is_dir($targetDir)) {
            $output->writeln(sprintf('<error>Target directory does not exist: %s</error>', $targetDir));

            return 1;
        }

        $basename = date('Y_m_d_His') . '_' . $slug;
        $path     = $targetDir . '/' . $basename . '.php';

        if (file_exists($path)) {
            $output->writeln(sprintf('<error>File already exists: %s</error>', $path));

            return 1;
        }

        $written = file_put_contents($path, $this->renderStub());

        if (false === $written) {
            throw new RuntimeException(sprintf('Failed to write migration to %s', $path));
        }

        $output->writeln(sprintf('<info>Created: %s</info>', $path));

        return 0;
    }

    private function renderStub(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

use MyParcelNL\Pdk\App\Installer\Migration\AbstractTimestampedMigration;

return new class extends AbstractTimestampedMigration {
    public function up(): void
    {
        // @TODO: implement
    }

    public function down(): void
    {
        // @TODO: implement (optional)
    }
};
PHP;
    }
}
