<?php

declare(strict_types=1);

namespace Inowas\Common\Grid;

use Inowas\Common\Geometry\Point;

class BoundingBox implements \JsonSerializable
{

    /** @var  Point */
    private $point1;

    /** @var  Point */
    private $point2;

    public static function fromPoints(Point $point1, Point $point2) {
        return new self($point1, $point2);
    }

    public static function fromCoordinates($x1, $x2, $y1, $y2): BoundingBox
    {
        $p1 = new Point($x1, $y1);
        $p2 = new Point($x2, $y2);
        return new self($p1, $p2);
    }

    public static function fromArray(array $bb): BoundingBox
    {
        $p1 = new Point($bb[0][0], $bb[0][1]);
        $p2 = new Point($bb[1][0], $bb[1][1]);

        return new self($p1, $p2);
    }

    private function __construct(Point $point1, Point $point2)
    {
        $this->point1 = $point1;
        $this->point2 = $point2;
    }

    public function xMin(): float
    {
        if ($this->point1->getX() <= $this->point2->getX()){
            return $this->point1->getX();
        }

        return $this->point2->getX();
    }

    public function xMax(): float
    {
        if ($this->point1->getX() >= $this->point2->getX()){
            return $this->point1->getX();
        }

        return $this->point2->getX();
    }

    public function yMin(): float
    {
        if ($this->point1->getY() <= $this->point2->getY()){
            return $this->point1->getY();
        }

        return $this->point2->getY();
    }

    public function yMax(): float
    {
        if ($this->point1->getY() > $this->point2->getY()){
            return $this->point1->getY();
        }

        return $this->point2->getY();
    }

    public function toArray()
    {
        return array(
            [$this->point1->getLongitude(), $this->point1->getLatitude()],
            [$this->point2->getLongitude(), $this->point2->getLatitude()]
        );
    }

    public function toGeoJson(){
        return sprintf('{"type":"Polygon", "coordinates":[[[%f,%f],[%f,%f],[%f,%f],[%f,%f],[%f,%f]]]}',
            $this->xMin(), $this->yMin(),
            $this->xMin(), $this->yMax(),
            $this->xMax(), $this->yMax(),
            $this->xMax(), $this->yMin(),
            $this->xMin(), $this->yMin()
        );
    }

    public function topLeft(): Point
    {
        return new Point($this->xMin(), $this->yMax());
    }

    public function topRight(): Point
    {
        return new Point($this->xMax(), $this->yMax());
    }

    public function bottomLeft(): Point
    {
        return new Point($this->xMin(), $this->yMin());
    }

    public function bottomRight(): Point
    {
        return new Point($this->xMax(), $this->yMin());
    }

    public function sameAs(BoundingBox $boundingBox): bool
    {
        return (
            ($this->xMin() === $boundingBox->xMin()) &&
            ($this->xMax() === $boundingBox->xMax()) &&
            ($this->yMin() === $boundingBox->yMin()) &&
            ($this->yMax() === $boundingBox->yMax())
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
