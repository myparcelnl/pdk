<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Shipment\Service;

use MyParcelNL\Pdk\App\Api\Backend\PdkBackendActions;
use MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection;
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

class ShipmentUpdateService
{
    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface
     */
    private $pdkOrderRepository;

    /**
     * @var \MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface
     */
    private $pdkOrderNoteRepository;

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface     $pdkOrderRepository
     * @param  \MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface $pdkOrderNoteRepository
     */
    public function __construct(
        PdkOrderRepositoryInterface     $pdkOrderRepository,
        PdkOrderNoteRepositoryInterface $pdkOrderNoteRepository
    ) {
        $this->pdkOrderRepository     = $pdkOrderRepository;
        $this->pdkOrderNoteRepository = $pdkOrderNoteRepository;
    }

    /**
     * @param  \MyParcelNL\Pdk\App\Order\Collection\PdkOrderCollection    $orders
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection     $shipments
     * @param  null|string                                                $orderStatus
     * @param  bool                                                       $linkFirstShipmentToFirstOrder
     *
     * @return void
     */
    public function update(
        PdkOrderCollection $orders,
        ShipmentCollection $shipments,
        ?string            $orderStatus = OrderSettings::STATUS_ON_LABEL_CREATE,
        bool               $linkFirstShipmentToFirstOrder = false
    ): void {
        if ($linkFirstShipmentToFirstOrder && $orders->isNotEmpty() && $shipments->isNotEmpty()) {
            $shipments->first()->orderId = $orders->first()->getExternalIdentifier();
        }

        if ($orders->isEmpty()) {
            return;
        }

        $orders->updateShipments($shipments);
        $this->pdkOrderRepository->updateMany($orders);

        $this->addBarcodeNotes($shipments);
        $this->updateOrderStatus($shipments, $orderStatus);
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
                if (! $shipment->orderId || ! $shipment->externalIdentifier || ! $shipment->barcode) {
                    return;
                }

                $pdkOrder       = $this->pdkOrderRepository->get($shipment->orderId);
                $noteIdentifier = Pdk::get('createBarcodeNoteIdentifier')($shipment->externalIdentifier);

                $notes = $this->pdkOrderNoteRepository->getFromOrder($pdkOrder);

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

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipments
     * @param  null|string                                            $status
     *
     * @return void
     */
    private function updateOrderStatus(ShipmentCollection $shipments, ?string $status): void
    {
        $shipments
            ->each(function (Shipment $shipment) use ($status) {
                $resolvedStatus = $status ?? OrderSettings::getStatus((int) $shipment->status);

                Logger::debug('Update status', [
                    'orderId' => $shipment->orderId,
                    'status'  => $resolvedStatus,
                ]);
                Actions::execute(PdkBackendActions::UPDATE_ORDER_STATUS, [
                    'orderIds' => [$shipment->orderId],
                    'setting'  => $resolvedStatus,
                ]);
            });
    }
}
