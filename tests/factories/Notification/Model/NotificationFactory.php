<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Notification\Model;

use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of Notification
 * @method Notification make()
 * @method $this withCategory(string $category)
 * @method $this withContent(string $content)
 * @method $this withTimeout(bool $timeout)
 * @method $this withTitle(string $title)
 * @method $this withVariant(string $variant)
 */
final class NotificationFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return Notification::class;
    }
}
