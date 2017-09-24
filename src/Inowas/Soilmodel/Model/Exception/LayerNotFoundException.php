<?php

namespace Inowas\Soilmodel\Model\Exception;

use Inowas\Common\Grid\LayerNumber;

final class LayerNotFoundException extends \InvalidArgumentException
{
    public static function withLayerNumber(LayerNumber $layerNumber): LayerNotFoundException
    {
        return new self(sprintf('Soilmodel has no Layer with layer number  %s.', $layerNumber->toInt()));
    }
}
