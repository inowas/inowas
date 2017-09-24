<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ObservationPointId;
use Inowas\Common\Modflow\Name;

final class BoundaryList
{

    /** @var  array */
    private $items;

    public static function create(): BoundaryList
    {
        return new self();
    }

    private function __construct(array $items = [])
    {
        $this->items = $items;
    }

    public function addItem(BoundaryListItem $item): BoundaryList
    {
        $this->items[] = $item->toArray();
        return new self($this->items);
    }

    public function toArray(): array
    {
        return $this->items;
    }
}
