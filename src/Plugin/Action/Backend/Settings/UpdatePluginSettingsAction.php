<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Backend\Settings;

use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\Plugin\Action\ActionInterface;
use MyParcelNL\Pdk\Settings\Model\Settings;
use MyParcelNL\Pdk\Settings\Repository\SettingsRepositoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdatePluginSettingsAction implements ActionInterface
{
    /**
     * @var \MyParcelNL\Pdk\Settings\Repository\SettingsRepositoryInterface
     */
    private $settingsRepository;

    /**
     * @param  \MyParcelNL\Pdk\Settings\Repository\SettingsRepositoryInterface $settingsRepository
     */
    public function __construct(SettingsRepositoryInterface $settingsRepository)
    {
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \MyParcelNL\Pdk\Base\Exception\InvalidCastException
     */
    public function handle(Request $request): Response
    {
        $body           = json_decode($request->getContent(), true);
        $pluginSettings = $body['data']['plugin_settings'] ?? [];

        if (empty($pluginSettings)) {
            throw new InvalidArgumentException('Request body is empty');
        }

        $settings = new Settings($pluginSettings);

        foreach (array_keys($pluginSettings) as $editedSettingsId) {
            $this->settingsRepository->storeSettings($settings->getAttribute($editedSettingsId));
        }

        return new JsonResponse([
            'data' => [
                'plugin_settings' => $settings->toArray(),
            ],
        ]);
    }
}
