<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Repository;

use MyParcelNL\Pdk\Base\Repository\ApiRepository;
use MyParcelNL\Pdk\Base\Response\PostIdsResponse;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;
use MyParcelNL\Pdk\Shipment\Model\Label;
use MyParcelNL\Pdk\Shipment\Request\FetchShipmentsRequest;
use MyParcelNL\Pdk\Shipment\Request\GetLabelsAsPdfRequest;
use MyParcelNL\Pdk\Shipment\Request\GetLabelsRequest;
use MyParcelNL\Pdk\Shipment\Request\GetShipmentsRequest;
use MyParcelNL\Pdk\Shipment\Request\PostReturnShipmentsRequest;
use MyParcelNL\Pdk\Shipment\Request\PostShipmentsRequest;
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
        /** @var \MyParcelNL\Pdk\Base\Response\PostIdsResponse $response */
        $response = $this->api->doRequest(new PostShipmentsRequest($collection), PostIdsResponse::class);

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
        /** @var \MyParcelNL\Pdk\Base\Response\PostIdsResponse $response */
        $response = $this->api->doRequest(
        // TODO: Make send_return_mail depend on plugin settings
            new PostReturnShipmentsRequest($collection, ['send_return_mail' => 1]),
            PostIdsResponse::class
        );

        $returnIds = $response->getIds()
            ->pluck('id')
            ->toArray();

        return $this->getShipments($returnIds);
    }

    /**
     * Fetch label link from api and fill the label->link property of the collection
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
    public function fetchLabelLink(
        ShipmentCollection $collection,
        ?string            $format,
        ?array             $position = null
    ): ShipmentCollection {
        $request = new GetLabelsRequest($collection, [
            'format'    => $format,
            'positions' => $position,
        ]);

        $this->retrieve($request->getUniqueKey(), function () use ($request, $collection) {
            /** @var \MyParcelNL\Pdk\Shipment\Response\GetLabelsResponse $response */
            $response = $this->api->doRequest($request, GetLabelsResponse::class);

            $collection->label       = $collection->label ?? new Label();
            $collection->label->link = $this->api->getBaseUrl() . $response->getLink();
        });

        return $collection;
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
    ): ShipmentCollection {
        $request = new GetLabelsAsPdfRequest($collection, [
            'format'    => $format,
            'positions' => $position,
        ]);

        $this->retrieve($request->getUniqueKey(), function () use ($request, $collection) {
            /** @var \MyParcelNL\Pdk\Shipment\Response\GetLabelsPdfResponse $response */
            $response = $this->api->doRequest($request, GetLabelsPdfResponse::class);

            $collection->label      = $collection->label ?? new Label();
            $collection->label->pdf = $response->getPdf();
        });

        return $collection;
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
