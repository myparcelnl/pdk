<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Order;

use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Facade\Settings as SettingsFacade;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Settings\Model\Settings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateOrderStatusAction extends AbstractOrderAction
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface
     */
    private $orderStatusService;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface $orderStatusService
     */
    public function __construct(
        PdkOrderRepositoryInterface $pdkOrderRepository,
        OrderStatusServiceInterface $orderStatusService
    ) {
        parent::__construct($pdkOrderRepository);

        $this->orderStatusService = $orderStatusService;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *                                                   
     * @todo INT-944 this allows an array of orderIds in the request, but the action only works with one orderId
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
