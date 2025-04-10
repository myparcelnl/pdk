<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Repository;

use MyParcelNL\Pdk\Api\Response\PostIdsResponse;
use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Facade\Settings;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Settings\Model\OrderSettings;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Request\FetchShipmentsRequest;
use MyParcelNL\Pdk\Shipment\Request\GetLabelsAsPdfRequest;
use MyParcelNL\Pdk\Shipment\Request\GetLabelsRequest;
use MyParcelNL\Pdk\Shipment\Request\GetShipmentsRequest;
use MyParcelNL\Pdk\Shipment\Request\PostReturnShipmentsRequest;
use MyParcelNL\Pdk\Shipment\Request\PostShipmentsRequest;
use MyParcelNL\Pdk\Shipment\Request\PrintShipmentsRequest;
use MyParcelNL\Pdk\Shipment\Response\GetLabelsPdfResponse;
use MyParcelNL\Pdk\Shipment\Response\GetLabelsResponse;
use MyParcelNL\Pdk\Shipment\Response\GetShipmentsResponse;

class ShipmentRepository extends ApiRepository
{
    /**
     * @noinspection PhpUnused
     *
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     * @noinspection PhpUnused
     */
    public function createConcepts(ShipmentCollection $collection): ShipmentCollection
    {
        $request = Settings::get(LabelSettings::DIRECT_PRINT, LabelSettings::ID)
            ? new PrintShipmentsRequest($collection) : new PostShipmentsRequest($collection);
        /** @var \MyParcelNL\Pdk\Api\Response\PostIdsResponse $response */
        $response = $this->api->doRequest($request, PostIdsResponse::class);

        return $collection->addIds($response->getIds());
    }

    /**
     * @noinspection PhpUnused
     *
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     * @throws \Exception
     */
    public function createReturnShipments(ShipmentCollection $collection): ShipmentCollection
    {
        $sendReturnEmail = Settings::get(
            OrderSettings::SEND_RETURN_EMAIL,
            OrderSettings::ID
        ) ? 1 : 0;
        /** @var \MyParcelNL\Pdk\Api\Response\PostIdsResponse $response */
        $response = $this->api->doRequest(
            new PostReturnShipmentsRequest($collection, ['send_return_mail' => $sendReturnEmail]),
            PostIdsResponse::class
        );

        $returnIds = $response->getIds()
            ->pluck('id')
            ->all();

        return $this->getShipments($returnIds);
    }

    /**
     * Fetch label link from api.
     *
     * @noinspection PhpUnused
     *
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     * @param  null|string                                            $format
     * @param  null|array                                             $position
     *
     * @return string
     */
    public function fetchLabelLink(
        ShipmentCollection $collection,
        ?string            $format,
        ?array             $position = null
    ): string {
        $request = new GetLabelsRequest($collection, [
            'format'    => $format,
            'positions' => $position,
        ]);

        return $this->retrieve($request->getUniqueKey(), function () use ($request) {
            /** @var \MyParcelNL\Pdk\Shipment\Response\GetLabelsResponse $response */
            $response = $this->api->doRequest($request, GetLabelsResponse::class);

            return $this->api->getBaseUrl() . $response->getLink();
        });
    }

    /**
     * Fetch label pdf from api and fill the label->pdf property of the collection
     *
     * @noinspection PhpUnused
     *
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     * @param  null|string                                            $format
     * @param  null|array                                             $position
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     * @noinspection PhpUnused
     */
    public function fetchLabelPdf(
        ShipmentCollection $collection,
        ?string            $format,
        ?array             $position = null
    ): string {
        $request = new GetLabelsAsPdfRequest($collection, [
            'format'    => $format,
            'positions' => $position,
        ]);

        return $this->retrieve($request->getUniqueKey(), function () use ($request) {
            /** @var \MyParcelNL\Pdk\Shipment\Response\GetLabelsPdfResponse $response */
            $response = $this->api->doRequest($request, GetLabelsPdfResponse::class);

            return $response->getPdf();
        });
    }

    /**
     * @param  array    $referenceIdentifiers
     * @param  null|int $size
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     * @noinspection PhpUnused
     */
    public function getByReferenceIdentifiers(array $referenceIdentifiers, ?int $size = null): ShipmentCollection
    {
        return $this->query(['reference_identifier' => $referenceIdentifiers, 'size' => $size]);
    }

    /**
     * @param  array $ids
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    public function getShipments(array $ids): ShipmentCollection
    {
        $request = new GetShipmentsRequest($ids);

        return $this->retrieve($request->getUniqueKey(), function () use ($request) {
            /** @var \MyParcelNL\Pdk\Shipment\Response\GetShipmentsResponse $response */
            $response = $this->api->doRequest($request, GetShipmentsResponse::class);

            return $response->getShipments();
        });
    }

    /**
     * @param  array $parameters
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     * @noinspection PhpUnused
     */
    public function query(array $parameters): ShipmentCollection
    {
        $request = new GetShipmentsRequest(['parameters' => $parameters]);

        return $this->retrieve($request->getUniqueKey(), function () use ($request) {
            /** @var \MyParcelNL\Pdk\Shipment\Response\GetShipmentsResponse $response */
            $response = $this->api->doRequest($request, GetShipmentsResponse::class);

            return $response->getShipments();
        });
    }

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $collection
     * @param  null|int                                               $size
     *
     * @return \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     * @noinspection PhpUnused
     */
    public function update(ShipmentCollection $collection, ?int $size = null): ShipmentCollection
    {
        $request = new FetchShipmentsRequest($collection, $size);

        return $this->retrieve($request->getUniqueKey(), function () use ($request) {
            /** @var \MyParcelNL\Pdk\Shipment\Response\GetShipmentsResponse $response */
            $response = $this->api->doRequest($request, GetShipmentsResponse::class);

            return $response->getShipments();
        });
    }
}
