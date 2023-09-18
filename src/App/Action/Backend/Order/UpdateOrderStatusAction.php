<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\Facade\Settings as SettingsFacade;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateOrderStatusAction extends AbstractOrderAction
{
    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $settingName = $request->get('setting');

        if (! $settingName) {
            return $this->getResponse();
        }

        $status = SettingsFacade::get($settingName, OrderSettings::ID);

        if (Settings::OPTION_NONE === (int) $status) {
            return $this->getResponse();
        }

        $orderIds = $this->getOrderIds($request);

        $this->orderStatusService->updateStatus($orderIds, $status);

        return $this->getResponse();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    private function getResponse(): Response
    {
        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
