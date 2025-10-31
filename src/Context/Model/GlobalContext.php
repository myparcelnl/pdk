<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Api\Contract\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Request\Collection\EndpointRequestCollection;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Facade\Proposition;
use MyParcelNL\Pdk\Frontend\Service\FrontendRenderService;

/**
 * @property AppInfo                   $appInfo
 * @property string                    $baseUrl
 * @property string                    $bootstrapId
 * @property EndpointRequestCollection $endpoints
 * @property string                    $eventPing
 * @property string                    $eventPong
 * @property string                    $language
 * @property string                    $mode
 * @property array                     $proposition      (NEW - preferred)
 * @property array                     $platform         (LEGACY - deprecated but supported)
 * @property array{string, string}     $translations
 */
class GlobalContext extends Model
{
    public    $attributes = [
        'appInfo'      => null,
        'baseUrl'      => null,
        'bootstrapId'  => FrontendRenderService::BOOTSTRAP_CONTAINER_ID,
        'endpoints'    => EndpointRequestCollection::class,
        'eventPing'    => FrontendRenderService::BOOTSTRAP_RENDER_EVENT_PING,
        'eventPong'    => FrontendRenderService::BOOTSTRAP_RENDER_EVENT_PONG,
        'language'     => null,
        'mode'         => null,
        'proposition'  => [],
        'platform'     => [],  // LEGACY
        'translations' => [],
    ];

    protected $casts      = [
        'appInfo'      => AppInfo::class,
        'baseUrl'      => 'string',
        'bootstrapId'  => 'string',
        'endpoints'    => EndpointRequestCollection::class,
        'eventPing'    => 'string',
        'eventPong'    => 'string',
        'language'     => 'string',
        'mode'         => 'string',
        'proposition'  => 'array',
        'platform'     => 'array',  // LEGACY
        'translations' => 'array',
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        parent::__construct($data);

        $this->attributes['appInfo']      = Pdk::getAppInfo();
        $this->attributes['mode']         = Pdk::getMode();
        $this->attributes['language']     = Language::getIso2();
        $this->attributes['translations'] = Language::getTranslations();

        /** @var \MyParcelNL\Pdk\App\Api\Contract\EndpointServiceInterface $endpointActions */
        $endpointActions = Pdk::get(BackendEndpointServiceInterface::class);

        $this->attributes['baseUrl']   = $endpointActions->getBaseUrl();
        $this->attributes['endpoints'] = $endpointActions->toArray();

        $propositionData = Proposition::all();

        $filteredData = array_intersect_key(
            $propositionData,
            array_flip([
                'name',
                'human',
                'backofficeUrl',
                'supportUrl',
                'localCountry',
                'defaultCarrier',
                'defaultCarrierId',
            ])
        );

        $this->attributes['proposition'] = $filteredData;

        // LEGACY: Also set platform for backward compatibility
        $this->attributes['platform'] = $filteredData;
    }
}
