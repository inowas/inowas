<?php

declare(strict_types=1);

namespace Inowas\Common\Geometry;

class Geometry implements \JsonSerializable
{
    /** @var  AbstractGeometry */
    private $geometry;

    public static $availableTypes = ['point', 'linestring', 'polygon'];

    public static function fromJson(string $json): Geometry
    {
        /*
         * {"type":"Point","coordinates":[105.86406114811,20.963857515931]}
         * {"type":"LineString","coordinates":[[105.78304910628,21.093961475741],[105.79076773351,21.094425931588]]}"
         * {"type":"Polygon","coordinates":[[[-63.65,-31.31],[-63.65,-31.36],[-63.58,-31.36],[-63.58,-31.31],[-63.65,-31.31]]]}"
         */
        $obj = json_decode($json);
        $type = strtolower($obj->type);

        if ($type === 'point') {
            return Geometry::fromPoint(new Point($obj->coordinates[0], $obj->coordinates[1]));
        }

        if ($type === 'linestring') {
            return Geometry::fromLineString(new LineString($obj->coordinates));
        }

        if ($type === 'polygon') {
            return Geometry::fromPolygon(new Polygon($obj->coordinates));
        }

        return null;
    }

    public static function fromPolygon(Polygon $polygon): Geometry
    {
        $self = new self();
        $self->geometry = $polygon;
        return $self;
    }

    public static function fromLineString(LineString $lineString): Geometry
    {
        $self = new self();
        $self->geometry = $lineString;
        return $self;
    }

    public static function fromArray(array $arr): Geometry
    {
        $type = strtolower($arr['type']);
        $coordinates = $arr['coordinates'];

        $srid = null;
        if (array_key_exists('srid', $arr)){
            $srid = $arr['srid'];
        }


        if ($type === 'point') {
            return Geometry::fromPoint(new Point($coordinates, $srid));
        }

        if ($type === 'linestring') {
            return Geometry::fromLineString(new LineString($coordinates, $srid));
        }

        if ($type === 'polygon') {
            return Geometry::fromPolygon(new Polygon($coordinates, $srid));
        }

        return null;
    }

    public static function fromPoint(Point $point): Geometry
    {
        $self = new self();
        $self->geometry = $point;
        return $self;
    }

    public static function isValid(array $arr): bool
    {
        if (! array_key_exists('type', $arr)) {
            return false;
        }

        if (! in_array(strtolower($arr['type']), self::$availableTypes, true)) {
            return false;
        }

        if (! array_key_exists('coordinates', $arr)) {
            return false;
        }

        if (! is_array($arr['coordinates'])) {
            return false;
        }

        return true;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->geometry->getType(),
            'coordinates' => $this->geometry->toArray(),
            'srid' => $this->geometry->getSrid()
        ];
    }

    public function toJson():string
    {
        return $this->geometry->toJson();
    }

    public function srid(): Srid
    {
        if (null === $this->geometry->getSrid()){
            Srid::fromInt(4326);
        }
        return Srid::fromInt($this->geometry->getSrid());
    }

    public function value()
    {
        return $this->geometry;
    }

    public function isPoint(): bool
    {
        return ($this->value() instanceof Point);
    }

    public function getPoint(): ?Point
    {
        if ($this->isPoint()) {
            return $this->value();
        }

        return null;
    }

    public function isLinestring(): bool
    {
        return ($this->value() instanceof LineString);
    }

    public function getLineString(): ?LineString
    {
        if ($this->isLinestring()){
            return $this->value();
        }

        return null;
    }

    public function isPolygon(): bool
    {
        return ($this->value() instanceof Polygon);
    }

    public function getPolygon(): ?Polygon
    {
        if ($this->isPolygon()){
            return $this->value();
        }

        return null;
    }

    public function getPointFromGeometry(): ?Point
    {
        if ($this->isPoint()) {
            return $this->value();
        }

        if ($this->isLinestring()) {
            /** @var LineString $linestring */
            $linestring = $this->value();
            return $linestring->getPoint(0);
        }

        if ($this->isPolygon()) {
            /** @var Polygon $polygon */
            $polygon = $this->value();
            return $polygon->getRing(0)->getPoint(0);
        }

        return null;
    }

    public function jsonSerialize()
    {
        return [
            'type' => $this->geometry->getType(),
            'coordinates' => $this->geometry->toArray(),
            'srid' => $this->srid()->toInteger()
        ];
    }
}
