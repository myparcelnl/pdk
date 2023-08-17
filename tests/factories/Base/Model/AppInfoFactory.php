<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Base\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of AppInfo
 * @method AppInfo make()
 * @method $this withName(string $name)
 * @method $this withPath(string $path)
 * @method $this withTitle(string $title)
 * @method $this withUrl(string $url)
 * @method $this withVersion(string $version)
 */
final class AppInfoFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return AppInfo::class;
    }
}
