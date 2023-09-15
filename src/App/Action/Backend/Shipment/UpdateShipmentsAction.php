<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\App\Action\Backend\Shipment;

use MyParcelNL\Pdk\Api\Response\JsonResponse;
use MyParcelNL\Pdk\App\Action\Backend\Order\AbstractOrderAction;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderNoteRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Contract\PdkOrderRepositoryInterface;
use MyParcelNL\Pdk\App\Order\Model\PdkOrderNote;
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
    public function __construct(
        PdkOrderRepositoryInterface                      $pdkOrderRepository,
        private readonly ShipmentRepository              $shipmentRepository,
        private readonly PdkOrderNoteRepositoryInterface $pdkOrderNoteRepository
    ) {
        parent::__construct($pdkOrderRepository);
    }

    public function handle(Request $request): Response
    {
        $orders    = $this->pdkOrderRepository->getMany($this->getOrderIds($request));
        $shipments = $this->shipmentRepository->getShipments($this->getShipmentIds($request, $orders));

        if ($orders->isNotEmpty()) {
            $orders->updateShipments($shipments);
            $this->pdkOrderRepository->updateMany($orders);

            $this->addBarcodeNotes($shipments);
        }

        return new JsonResponse([
            'shipments' => $shipments->toStorableArray(),
        ]);
    }

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
}

