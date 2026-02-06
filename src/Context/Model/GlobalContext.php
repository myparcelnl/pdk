<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Context\Model;

use MyParcelNL\Pdk\App\Api\Contract\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\App\Request\Collection\EndpointRequestCollection;
use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Language;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Platform;
use MyParcelNL\Pdk\Frontend\Service\FrontendRenderService;
use MyParcelNL\Pdk\Proposition\Service\PropositionService;

/**
 * @property AppInfo                   $appInfo
 * @property string                    $baseUrl
 * @property string                    $bootstrapId
 * @property EndpointRequestCollection $endpoints
 * @property string                    $eventPing
 * @property string                    $eventPong
 * @property string                    $language
 * @property string                    $mode
 * @property array                     $proposition
 * @property array                     $platform
 * @property array{string, string}     $translations
 */
class GlobalContext extends Model
{
    public $attributes = [
        'appInfo'      => null,
        'baseUrl'      => null,
        'bootstrapId'  => FrontendRenderService::BOOTSTRAP_CONTAINER_ID,
        'endpoints'    => EndpointRequestCollection::class,
        'eventPing'    => FrontendRenderService::BOOTSTRAP_RENDER_EVENT_PING,
        'eventPong'    => FrontendRenderService::BOOTSTRAP_RENDER_EVENT_PONG,
        'language'     => null,
        'mode'         => null,
        'proposition'  => [],
        'platform'     => [],
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
        'platform'     => 'array',
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

        try {
            $propositionService = Pdk::get(PropositionService::class);
            $proposition        = $propositionService->getPropositionConfig();

            $platformFiltered = array_intersect_key(
                $propositionService->mapToPlatformConfig($proposition),
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

            // Build a new-format proposition payload (not legacy-mapped)
            $defaultCarrier = $propositionService->getDefaultCarrier();
            $this->attributes['proposition'] = [
                'name'             => $proposition->proposition->key,
                'human'            => $proposition->proposition->name,
                'backofficeUrl'    => $proposition->applications['backoffice']['url'] ?? null,
                'supportUrl'       => $proposition->applications['developerPortal']['url'] ?? null,
                'localCountry'     => $proposition->countryCode,
                'defaultCarrier'   => $defaultCarrier ? $defaultCarrier->name : null, // CONSTANT_CASE name
                'defaultCarrierId' => $defaultCarrier ? $defaultCarrier->id : null,
            ];
            // Keep legacy-mapped platform config for backwards compatibility
            $this->attributes['platform']    = $platformFiltered;
        } catch (\Throwable $throwable) {
            // Log and ignore, this may occur before setting an API key or when a new platform is not yet supported.
            Logger::alert(
                'Could not load proposition data: ' . $throwable->getMessage(),
                ['exception' => $throwable]
            );
        }
    }
}
