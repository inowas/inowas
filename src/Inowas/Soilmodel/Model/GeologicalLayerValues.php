<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model;

use Inowas\Common\Conductivity\LayerConductivity;
use Inowas\Common\Soilmodel\BottomElevation;
use Inowas\Common\Soilmodel\TopElevation;
use Inowas\Common\Soilmodel\Storage;

class GeologicalLayerValues
{
    /** @var BottomElevation */
    private $hBottom;

    /** @var TopElevation */
    private $hTop;

    /** @var LayerConductivity */
    private $conductivity;

    /** @var Storage */
    private $storage;


    public function hBottom(): BottomElevation
    {
        return $this->hBottom;
    }

    public function hTop(): TopElevation
    {
        return $this->hTop;
    }

    public function conductivity(): LayerConductivity
    {
        return $this->conductivity;
    }

    public function storage(): Storage
    {
        return $this->storage;
    }

    public static function fromParams(TopElevation $hTop, BottomElevation $hBot, LayerConductivity $conductivity, Storage $storage): GeologicalLayerValues
    {
        $self = new self();
        $self->hTop = $hTop;
        $self->hBottom = $hBot;
        $self->conductivity = $conductivity;
        $self->storage = $storage;
        return $self;
    }

    public static function fromArray(array $data): GeologicalLayerValues
    {
        $self = new self();
        $self->hTop = TopElevation::fromValue($data['h_top']);
        $self->hBottom = BottomElevation::fromValue($data['h_bot']);
        $self->conductivity = LayerConductivity::fromArray($data['conductivity']);
        $self->storage = Storage::fromArray($data['storage']);
        return $self;
    }

    public function toArray(): array
    {
        return array(
            'h_top' => $this->hTop->toValue(),
            'h_bot' => $this->hBottom->toValue(),
            'conductivity' => $this->conductivity->toArray(),
            'storage' => $this->storage->toArray()
        );
    }
}
