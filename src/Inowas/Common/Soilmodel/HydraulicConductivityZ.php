<?php

declare(strict_types=1);

namespace Inowas\Common\Soilmodel;

class HydraulicConductivityZ extends AbstractSoilproperty
{

    const TYPE = 'kz';

    public static function create(): HydraulicConductivityZ
    {
        return new self(null);
    }

    public static function fromPointValue($value): HydraulicConductivityZ
    {
        return new self($value);
    }

    public static function fromLayerValue($value): HydraulicConductivityZ
    {
        return new self($value, true);
    }

    public static function fromArray(array $arr): HydraulicConductivityZ
    {
        return new self($arr['value'], $arr['is_layer']);
    }

    public function toArray(): array
    {
        return array(
            'value' => $this->value,
            'is_layer' => $this->isLayer
        );
    }

    public function identifier(): string
        {
            return self::TYPE;
    }
}
