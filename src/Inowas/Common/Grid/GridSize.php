<?php

declare(strict_types=1);

namespace Inowas\Common\Grid;

use Inowas\ModflowModel\Model\Exception\GridSizeOutOfRangeException;

class GridSize implements \JsonSerializable
{

    private $nX;
    private $nY;

    public static function fromXY(int $nX, int $nY): GridSize
    {
        if ($nX<1) {
            throw GridSizeOutOfRangeException::withNXValue($nX);
        }

        if ($nY<1) {
            throw GridSizeOutOfRangeException::withNYValue($nY);
        }

        return new self($nX, $nY);
    }

    public static function fromArray(array $gridSizeArray): GridSize
    {
        if (! array_key_exists('n_x', $gridSizeArray)) {
            throw new \Exception();
        }

        if (! array_key_exists('n_y', $gridSizeArray))
        {
            throw new \Exception();
        }

        return new self($gridSizeArray['n_x'], $gridSizeArray['n_y']);
    }

    private function __construct(int $nX, int $nY)
    {
        $this->nX = $nX;
        $this->nY = $nY;
    }

    public function nX(): int
    {
        return $this->nX;
    }

    public function nY(): int
    {
        return $this->nY;
    }

    public function toArray(): array
    {
        return array(
            'n_x' => $this->nX,
            'n_y' => $this->nY,
        );
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function sameAs(GridSize $gridSize): bool
    {
        return (($this->nx() === $gridSize->nX()) && ($this->nY() === $gridSize->nY()));
    }
}
