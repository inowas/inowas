<?php

declare(strict_types=1);

namespace Inowas\Common\Geometry;

use \CrEOF\Spatial\PHP\Types\Geometry\Polygon as BasePolygon;

class Polygon extends BasePolygon
{

    public static function fromJson(string $json): Polygon
    {
        $obj = json_decode($json);
        $type = strtolower($obj->type);

        if ($type === 'polygon') {
            return new Polygon($obj->coordinates);
        }

        return null;
    }

    public function toJson(): string
    {
        return json_encode(array(
            'type' => $this->getType(),
            'coordinates' => $this->toArray(),
        ));
    }

    public function sameAs(Polygon $polygon): bool
    {
        return $polygon->toArray() === $this->toArray();
    }
}
