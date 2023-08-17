<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Settings\Model;

/**
 * @template T of LabelSettings
 * @method LabelSettings make()
 * @method $this withDescription(string $description)
 * @method $this withFormat(string $format)
 * @method $this withOutput(string $output)
 * @method $this withPosition(int[] $position)
 * @method $this withPrompt(bool $prompt)
 */
final class LabelSettingsFactory extends AbstractSettingsModelFactory
{
    public function getModel(): string
    {
        return LabelSettings::class;
    }
}
