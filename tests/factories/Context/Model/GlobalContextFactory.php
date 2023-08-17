<?php
/** @noinspection PhpUnused */

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Request\Collection\EndpointRequestCollection;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Base\Model\AppInfoFactory;
use MyParcelNL\Pdk\Tests\Factory\Model\AbstractModelFactory;

/**
 * @template T of GlobalContext
 * @method GlobalContext make()
 * @method $this withAppInfo(AppInfo|AppInfoFactory $appInfo)
 * @method $this withBaseUrl(string $baseUrl)
 * @method $this withBootstrapId(string $bootstrapId)
 * @method $this withEndpoints(EndpointRequestCollection $endpoints)
 * @method $this withEventPing(string $eventPing)
 * @method $this withEventPong(string $eventPong)
 * @method $this withLanguage(string $language)
 * @method $this withMode(string $mode)
 * @method $this withPlatform(array $platform)
 * @method $this withTranslations($translations)
 */
final class GlobalContextFactory extends AbstractModelFactory
{
    public function getModel(): string
    {
        return GlobalContext::class;
    }
}
