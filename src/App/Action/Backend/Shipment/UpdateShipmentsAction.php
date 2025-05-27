<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Shipment;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Backend\Order\AbstractOrderAction;
use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
use MyParcelNL\Pdk\Facade\Actions;
use MyParcelNL\Pdk\Facade\Logger;
use MyParcelNL\Pdk\Facade\Pdk;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Shipment;
use MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateShipmentsAction extends AbstractOrderAction
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface
     */
    private $pdkOrderNoteRepository;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository
     */
    private $shipmentRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface     $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\Shipment\Repository\ShipmentRepository             $shipmentRepository
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface $pdkOrderNoteRepository
     */
    public function __construct(
        PdkOrderRepositoryInterface     $pdkOrderRepository,
        ShipmentRepository              $shipmentRepository,
        PdkOrderNoteRepositoryInterface $pdkOrderNoteRepository
    ) {
        parent::__construct($pdkOrderRepository);
        $this->shipmentRepository     = $shipmentRepository;
        $this->pdkOrderNoteRepository = $pdkOrderNoteRepository;
    }

    /**
     * @param  \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request): Response
    {
        $orders    = $this->pdkOrderRepository->getMany($this->getOrderIds($request));
        $shipments = $this->shipmentRepository->getShipments($this->getShipmentIds($request, $orders));

        if ($request->get('linkFirstShipmentToFirstOrder')
            && $orders->isNotEmpty()
            && $shipments->isNotEmpty()
        ) {
            $shipments->first()->orderId = $orders->first()->getExternalIdentifier();
        }

        if ($orders->isNotEmpty()) {
            $orders->updateShipments($shipments);
            $this->pdkOrderRepository->updateMany($orders);

            $this->addBarcodeNotes($shipments);

            $this->updateOrderStatus($shipments, $request->get('orderStatus', OrderSettings::STATUS_ON_LABEL_CREATE));
        }

        return new JsonResponse([
            'shipments' => $shipments->toStorableArray(),
        ]);
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     *
     * @return void
     */
    private function addBarcodeNotes(ShipmentCollection $shipments): void
    {
        if (! Settings::get(OrderSettings::BARCODE_IN_NOTE, OrderSettings::ID)) {
            return;
        }

        $prefix = Settings::get(OrderSettings::BARCODE_IN_NOTE_TITLE, OrderSettings::ID);

        $shipments
            ->each(function (Shipment $shipment) use ($prefix) {
                $pdkOrder       = $this->pdkOrderRepository->get($shipment->orderId);
                $noteIdentifier = Pdk::get('createBarcodeNoteIdentifier')($shipment->externalIdentifier);

                $notes = $this->pdkOrderNoteRepository->getFromOrder($pdkOrder);

                // if note with below externalIdentifier already exists, skip
                if ($notes->firstWhere('externalIdentifier', $noteIdentifier)) {
                    return;
                }

                $note = new PdkOrderNote([
                    'externalIdentifier' => $noteIdentifier,
                    'orderIdentifier'    => $shipment->orderId,
                    'author'             => 'pdk',
                    'note'               => trim("$prefix $shipment->barcode"),
                ]);

                $this->pdkOrderNoteRepository->add($note);
            });
    }

    private function updateOrderStatus(ShipmentCollection $shipments, string $status): void
    {
        $shipments
            ->each(function (Shipment $shipment) use ($status) {
                Logger::debug('Update status', [
                    'orderId' => $shipment->orderId,
                    'status'  => $status,
                ]);
                Actions::execute(PdkBackendActions::UPDATE_ORDER_STATUS, [
                    'orderIds' => [$shipment->orderId],
                    'setting'  => $status,
                ]);
            });
    }
}
