<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Shipment;

use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Backend\Order\AbstractOrderAction;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
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

    public function __construct(PdkOrderRepositoryInterface $pdkOrderRepository, ShipmentRepository $shipmentRepository)
    {
        parent::__construct($pdkOrderRepository);
        $this->shipmentRepository = $shipmentRepository;
    }

    public function handle(Request $request): Response
    {
        $format    = strtoupper(
            (string) $this->getLabelOption($request, LabelSettings::FORMAT, LabelSettings::DEFAULT_FORMAT)
        );
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

        return match ($output) {
            LabelSettings::OUTPUT_OPEN => $this->getPdf($shipments, $format, $positions),
            LabelSettings::OUTPUT_DOWNLOAD => $this->getUrlToPdf($shipments, $format, $positions),
            default => throw new InvalidArgumentException("Invalid output type $output"),
        };
    }

    /**
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

    protected function getUrlToPdf(ShipmentCollection $shipments, string $format, array $position): Response
    {
        return new JsonResponse([
            'pdfs' => [
                'url' => $this->shipmentRepository->fetchLabelLink($shipments, $format, $position),
            ],
        ]);
    }

    /**
     * @return mixed
     */
    private function getLabelOption(Request $request, string $name, mixed $default = null)
    {
        return $request->get($name, Settings::get($name, LabelSettings::ID, $default));
    }
}
