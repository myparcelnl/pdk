<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Model\AppInfo;
use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\LanguageService;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Api\Backend\BackendEndpointServiceInterface;
use MyParcelNL\Pdk\Plugin\Collection\EndpointRequestCollection;
use MyParcelNL\Pdk\Plugin\Service\RenderService;

/**
 * @property AppInfo                                                     $appInfo
 * @property string                                                      $baseUrl
 * @property string                                                      $bootstrapId
 * @property \MyParcelNL\Pdk\Plugin\Collection\EndpointRequestCollection $endpoints
 * @property string                                                      $event
 * @property string                                                      $language
 * @property string                                                      $mode
 * @property array{string, string}                                       $translations
 */
class GlobalContext extends Model
{
    public    $attributes = [
        'appInfo'      => null,
        'baseUrl'      => null,
        'bootstrapId'  => RenderService::BOOTSTRAP_CONTAINER_ID,
        'endpoints'    => EndpointRequestCollection::class,
        'event'        => RenderService::BOOTSTRAP_RENDER_EVENT,
        'language'     => null,
        'mode'         => null,
        'translations' => [],
    ];

    protected $casts      = [
        'appInfo'      => AppInfo::class,
        'baseUrl'      => 'string',
        'bootstrapId'  => 'string',
        'endpoints'    => EndpointRequestCollection::class,
        'event'        => 'string',
        'language'     => 'string',
        'mode'         => 'string',
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
        $this->attributes['language']     = LanguageService::getIso2();
        $this->attributes['translations'] = LanguageService::getTranslations();

        /** @var \MyParcelNL\Pdk\Plugin\Api\EndpointServiceInterface $endpointActions */
        $endpointActions = Pdk::get(BackendEndpointServiceInterface::class);

        $this->attributes['baseUrl']   = $endpointActions->getBaseUrl();
        $this->attributes['endpoints'] = $endpointActions->toArray();
    }
}
