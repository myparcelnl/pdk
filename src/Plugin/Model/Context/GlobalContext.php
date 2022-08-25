<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Model\Context;

use MyParcelNL\Pdk\Base\Model\Model;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Plugin\Service\RenderService;

/**
 * @property string                                                         $baseUrl
 * @property string                                                         $bootstrapId
 * @property \MyParcelNL\Pdk\Plugin\Model\Context\EndpointRequestCollection $endpoints
 * @property string                                                         $event
 * @property string                                                         $mode
 * @property array{string, string}                                          $pluginSettings
 * @property array{string, string}                                          $translations
 */
class GlobalContext extends Model
{
    public    $attributes = [
        'baseUrl'        => null,
        'bootstrapId'    => RenderService::BOOTSTRAP_DATA_CONTAINER_ID,
        'endpoints'      => EndpointRequestCollection::class,
        'event'          => RenderService::BOOTSTRAP_RENDER_EVENT,
        'mode'           => null,
        'pluginSettings' => [],
        'translations'   => [],
    ];

    protected $casts      = [
        'baseUrl'        => 'string',
        'bootstrapId'    => 'string',
        'endpoints'      => EndpointRequestCollection::class,
        'event'          => 'string',
        'mode'           => 'string',
        'pluginSettings' => 'array',
        'translations'   => 'array',
    ];

    /**
     * @param  null|array $data
     */
    public function __construct(?array $data = null)
    {
        $data['mode'] = Pdk::getMode();

        parent::__construct($data);
    }
}
