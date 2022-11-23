<?php

declare(strict_types=1);

namespace MyParcelNL\Pdk\Shipment\Request;

use MyParcelNL\Pdk\Base\Request\Request;
use MyParcelNL\Pdk\Settings\Model\LabelSettings;
use MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection;

class GetLabelsRequest extends Request
{
    public const  DEFAULT_POSITIONS = [2, 4, 1, 3];
    private const PATH              = 'shipment_labels/:ids';
    private const PATH_V2           = 'v2/shipment_labels/:ids';
    private const LIMIT_TO_USE_V2   = 25;

    /**
     * @var \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection
     */
    private $collection;

    /**
     * @param  \MyParcelNL\Pdk\Shipment\Collection\ShipmentCollection $shipmentCollection
     * @param  array                                                  $parameters
     */
    public function __construct(ShipmentCollection $shipmentCollection, array $parameters)
    {
        $this->collection        = $shipmentCollection;
        $positions               = array_slice(self::DEFAULT_POSITIONS, $parameters['positions'][0] % 4);
        $parameters['positions'] = $this->setLabelParameters($positions);
        parent::__construct(['parameters' => $parameters]);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers += ['Accept' => 'application/json;charset=utf8'];
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        $usesV2Endpoint = $this->hasBulkPrepare();
        $path           = $usesV2Endpoint ? self::PATH_V2 : self::PATH;
        $ids            = $this->collection
            ->pluck('id')
            ->toArray();

        return strtr($path, [':ids' => implode(';', $ids)]);
    }

    /**
     * @return array
     */
    protected function getParameters(): array
    {
        $parameters              = $this->parameters;
        $parameters['positions'] = implode(';', $parameters['positions'] ?? []);
        return array_filter($parameters);
    }

    /**
     * Generating positions for A4 paper
     *
     * @param  array $parameters
     *
     * @return string
     */
    private function getPositions(array $parameters): string
    {
        $aPositions = [];
        switch ($parameters['position']) {
            /** @noinspection PhpMissingBreakStatementInspection */
            case 1:
                $aPositions[] = 1;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 2:
                $aPositions[] = 2;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 3:
                $aPositions[] = 3;
            /** @noinspection PhpMissingBreakStatementInspection */
            case 4:
                $aPositions[] = 4;
                break;
        }

        return implode(';', $aPositions);
    }

    /**
     * @return bool
     */
    private function hasBulkPrepare(): bool
    {
        return $this->collection->count() >= self::LIMIT_TO_USE_V2;
    }

    /**
     * Set label format settings        The position of the label on an A4 sheet. You can specify multiple positions by
     *                                  using an array. E.g. [2,3,4]. If you do not specify an array, but specify a
     *                                  number, the following labels will fill the ascending positions. Positioning is
     *                                  only applied on the first page with labels. All subsequent pages will use the
     *                                  default positioning [1,2,3,4].
     *
     * @param  int|array|null $positions
     *
     * @return array
     */
    private function setLabelParameters($positions): array
    {
        /** If $positions is not false, set paper size to A4 */
        if (is_numeric($positions)) {
            /** Generating positions for A4 paper */
            $format   = LabelSettings::FORMAT_A4;
            $position = $this->getPositions($positions);
        } elseif (is_array($positions)) {
            /** Set positions for A4 paper */
            $format   = 'A4';
            $position = implode(';', $positions);
        } else {
            /** Set paper size to A6 */
            $format   = 'A6';
            $position = null;
        }

        return [
            'format'   => $format,
            'position' => $position,
        ];
    }
}

