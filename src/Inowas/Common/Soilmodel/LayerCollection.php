<?php

declare(strict_types=1);

namespace Inowas\Common\Soilmodel;

class LayerCollection
{

    /** @var  array */
    private $layers = [];

    public static function create(): LayerCollection
    {
        return new self();
    }

    private function __construct()
    {}

    public function addLayer(Layer $layer): void
    {
        $this->layers[] = $layer;
    }

    public function toArray(): array
    {
        $layers = [];

        /** @var Layer $layer */
        foreach ($this->layers as $layer) {
            $layers[] = $layer->toArray();
        }
        return $layers;
    }

    public function toMetaDataArray(): array
    {
        $layers = [];

        /** @var Layer $layer */
        foreach ($this->layers as $layer) {
            $layers[] = $layer->toMetadataArray();
        }
        return $layers;
    }
}
