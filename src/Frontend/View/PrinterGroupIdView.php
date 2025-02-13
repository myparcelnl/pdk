<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Frontend\View;

use MyParcelNL\Pdk\Api\Contract\ApiServiceInterface;
use MyParcelNL\Pdk\Api\Request\Request;
use MyParcelNL\Pdk\Api\Response\ApiResponseWithBody;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Frontend\Form\Element\Concern\ElementBuilderWithOptionsInterface;
use MyParcelNL\Pdk\Frontend\Form\Element\SelectInput;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;

final class PrinterGroupIdView extends NewAbstractSettingsView
{
    /**
     * @return void
     */
    protected function addElements(): void
    {
        $request = new Request([
            'method' => 'GET',
            'path'   => 'printer-groups',
        ]);
        $api     = Pdk::get(ApiServiceInterface::class);
        $api->setBaseUrl(Pdk::get('printingApiUrl'));

        try {
            $response = $api->doRequest($request, ApiResponseWithBody::class);
            $groups   = json_decode($response->getBody(), false)->results;
        } catch (\Throwable $e) {
            Logger::error($e->getMessage());

            return;
        }

        $options  = [];
        foreach ($groups as $group) {
            $options[$group->id] = $group->name;
        }

        $this->formBuilder->add(
            (new SelectInput(LabelSettings::PRINTER_GROUP_ID))
                ->withOptions($options, ElementBuilderWithOptionsInterface::USE_PLAIN_LABEL)
                ->visibleWhen(LabelSettings::DIRECT_PRINT)
        );
    }

    /**
     * @return string
     */
    protected function getPrefix(): string
    {
        return 'direct_print';
    }
}
