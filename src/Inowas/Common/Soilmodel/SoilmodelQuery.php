<?php

namespace Inowas\Common\Soilmodel;


final class SoilmodelQuery
{

    /** @var Soilmodel */
    private $soilmodel;

    /** @var  LayerCollection */
    private $layers;

    public static function create(Soilmodel $soilmodel, LayerCollection $layers): SoilmodelQuery
    {
        return new self($soilmodel, $layers);
    }

    private function __construct(Soilmodel $soilmodel, LayerCollection $layers)
    {
        $this->soilmodel = $soilmodel;
        $this->layers = $layers;
    }

    public function toGeneralArray(): array
    {
        return array(
            'general' => $this->soilmodel->toArray(),
            'layers' => $this->layers->toMetaDataArray(),
        );
    }
}
