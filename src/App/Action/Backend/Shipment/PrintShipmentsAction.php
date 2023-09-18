<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Shipment;

use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Backend\Order\AbstractOrderAction;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PrintShipmentsAction extends AbstractOrderAction
{
    /**
     * @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository
     */
    protected $shipmentRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository         $shipmentRepository
     * @param  \MyParcelNL\Pdk\App\Order\Contract\OrderStatusServiceInterface $orderStatusService
     */
    public function __construct(
        PdkOrderRepositoryInterface $pdkOrderRepository,
        ShipmentRepository          $shipmentRepository,
        OrderStatusServiceInterface $orderStatusService
    ) {
        parent::__construct($pdkOrderRepository, $orderStatusService);
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $format    = strtoupper($this->getLabelOption($request, LabelSettings::FORMAT, LabelSettings::DEFAULT_FORMAT));
        $output    = $this->getLabelOption($request, LabelSettings::OUTPUT, LabelSettings::DEFAULT_OUTPUT);
        $positions = Utils::toArray(
            $this->getLabelOption($request, LabelSettings::POSITION, LabelSettings::DEFAULT_POSITION)
        );

        $orderIds    = $this->getOrderIds($request);
        $orders      = $this->pdkOrderRepository->getMany($orderIds);
        $shipmentIds = $this->getShipmentIds($request, $orders);

        $shipments = count($shipmentIds)
            ? $orders->getShipmentsByIds($shipmentIds)
            : $orders->getLastShipments();

        Actions::execute(PdkBackendActions::UPDATE_ORDER_STATUS, [
            'orderIds' => $orderIds,
            'setting'  => OrderSettings::STATUS_ON_LABEL_CREATE,
        ]);

        switch ($output) {
            case LabelSettings::OUTPUT_OPEN:
                return $this->getPdf($shipments, $format, $positions);

            case LabelSettings::OUTPUT_DOWNLOAD:
                return $this->getUrlToPdf($shipments, $format, $positions);

            default:
                throw new InvalidArgumentException("Invalid output type $output");
        }
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     * @param  string                                                 $format
     * @param  array                                                  $position
     *
     * @return JsonResponse
     */
    protected function getPdf(ShipmentCollection $shipments, string $format, array $position): Response
    {
        $pdf = $this->shipmentRepository->fetchLabelPdf($shipments, $format, $position);

        return new JsonResponse([
            'pdfs' => [
                'data' => base64_encode($pdf),
            ],
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     * @param  string                                                 $format
     * @param  array                                                  $position
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function getUrlToPdf(ShipmentCollection $shipments, string $format, array $position): Response
    {
        return new JsonResponse([
            'pdfs' => [
                'url' => $this->shipmentRepository->fetchLabelLink($shipments, $format, $position),
            ],
        ]);
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     * @param  string                                    $name
     * @param  mixed                                     $default
     *
     * @return mixed
     */
    private function getLabelOption(Request $request, string $name, $default = null)
    {
        return $request->get($name, Settings::get($name, LabelSettings::ID, $default));
    }
}
