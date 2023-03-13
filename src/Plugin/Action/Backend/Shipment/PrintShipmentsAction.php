<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Plugin\Action\Backend\Shipment;

use InvalidArgumentException;
use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\Base\Support\Utils;
use MyParcelNL\Pdk\Plugin\Action\Backend\Order\AbstractOrderAction;
use MyParcelNL\Pdk\Plugin\Contract\PdkOrderRepositoryInterface;
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

    /**
     * @param  \MyParcelNL\Pdk\Plugin\Contract\PdkOrderRepositoryInterface $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository      $shipmentRepository
     */
    public function __construct(
        PdkOrderRepositoryInterface $pdkOrderRepository,
        ShipmentRepository          $shipmentRepository
    ) {
        parent::__construct($pdkOrderRepository);
        $this->shipmentRepository = $shipmentRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $orderIds  = $this->getOrderIds($request);
        $orders    = $this->pdkOrderRepository->getMany($orderIds);
        $output    = $request->get('output', LabelSettings::DEFAULT_OUTPUT);
        $position  = Utils::toArray($request->get('position', LabelSettings::DEFAULT_POSITION));
        $format    = $request->get('format', LabelSettings::DEFAULT_FORMAT);
        $shipments = $orders->getLastShipments();

        switch ($output) {
            case LabelSettings::OUTPUT_DOWNLOAD:
                return $this->downloadPdf($shipments, $format, $position);

            case LabelSettings::OUTPUT_OPEN:
                return $this->openPdf($shipments, $format, $position);

            default:
                throw new InvalidArgumentException('Invalid output type ' . $output);
        }
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     * @param  string                                                 $format
     * @param  array                                                  $position
     *
     * @return mixed
     */
    protected function downloadPdf(
        ShipmentCollection $shipments,
        string             $format,
        array              $position
    ): Response {
        $collection = $this->shipmentRepository->fetchLabelPdf($shipments, $format, $position);

        return new Response($collection->label->pdf, 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="labels.pdf"',
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     * @param  string                                                 $format
     * @param  array                                                  $position
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function openPdf(
        ShipmentCollection $shipments,
        string             $format,
        array              $position
    ): Response {
        $collection = $this->shipmentRepository->fetchLabelLink($shipments, $format, $position);

        return new JsonResponse([
            'pdfs' => [
                'url' => $collection->label->link,
            ],
        ]);
    }
}
