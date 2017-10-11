<?php

declare(strict_types=1);

namespace Inowas\Common\Grid;

class ActiveCells
{
    /**
     * Represents an 2D-Array of the active cells of a layer
     * @var array
     */
    private $layerData;

    /**
     * Represents the number of columns
     * @var int
     */
    private $nx = 0;

    /**
     * Represents the number of rows
     * @var int
     */
    private $ny = 0;

    /**
     * Represents the affected layers as a list of int
     * @var array
     */
    private $layers;

    public static function fromArrayAndGridSize(array $layerData, GridSize $gridSize): ActiveCells
    {
        return new self($layerData, [0], $gridSize);
    }

    public static function fromArrayGridSizeAndLayer(array $layerData, GridSize $gridSize, AffectedLayers $layers): ActiveCells
    {
        return new self($layerData, $layers->toArray(), $gridSize);
    }

    public static function fromArrayGridSizeAndLayers(array $layerData, GridSize $gridSize, array $layers): ActiveCells
    {
        return new self($layerData, $layers, $gridSize);
    }

    public static function from2DArray(array $arr): ActiveCells
    {
        $gridSize = GridSize::fromXY(count($arr), count($arr[0]));
        return new self($arr, [0], $gridSize);
    }

    public static function fromArray(array $arr): ActiveCells
    {
        $layerData = (array)$arr['data'];
        $gridSize = GridSize::fromXY($arr['n_x'], $arr['n_y']);
        $layers = $arr['layers'];
        return new self($layerData, $layers, $gridSize);
    }

    public static function fromCells(array $arr): ActiveCells
    {
        $layers = array();
        $layerData = array();
        foreach ($arr as $item){
            $layers = array($item[0]);
            $layerData[$item[1]] = array();
            $layerData[$item[1]][$item[2]] = true;
        }

        return new self($layerData, $layers);
    }

    public static function from2DCells(array $arr, GridSize $gridSize, AffectedLayers $affectedLayers): ActiveCells
    {
        $layers = $affectedLayers->toArray();

        $data = [];
        foreach ($arr as $item){
            $data[$item[0]] = array();
            $data[$item[0]][$item[1]] = true;
        }

        return new self($data, $layers, $gridSize);
    }

    private function __construct(array $layerData, array $layers, ?GridSize $gridSize = null)
    {
        $data = array();
        foreach ($layerData as $row => $cols){
            $data[$row] = array();
            foreach ($cols as $col => $value){
                $data[$row][$col] = (bool)$value;
            }
        }

        $this->layerData = $data;

        if ($gridSize instanceof GridSize)
        {
            $this->nx = $gridSize->nX();
            $this->ny = $gridSize->nY();
        }

        $this->layers = $layers;
    }

    /**
     * This function returns the active cells in an element each cell
     * [
     *      [$layer, $row, $col],
     *      [$layer, $row, $col],
     *      [$layer, $row, $col],
     *      ...
     * ]
     * @return array
     *
     */
    public function cells(): array
    {
        $cells = [];
        foreach ($this->layers as $layer) {
            foreach ($this->layerData as $rowNumber => $row) {
                foreach ($row as $colNumber => $isActive) {
                    if ($isActive === 1 || $isActive === true) {
                        $cells[] = [(int)$layer, (int)$rowNumber, (int)$colNumber];
                    }
                }
            }
        }

        return $cells;
    }

    public function cells2D(): array
    {
        $cells = [];

        foreach ($this->layerData as $rowNumber => $row) {
            foreach ($row as $colNumber => $isActive) {
                if ($isActive === 1 || $isActive === true) {
                    $cells[] = [(int)$rowNumber, (int)$colNumber];
                }
            }
        }

        return $cells;
    }

    public function gridSize(): GridSize
    {
        return GridSize::fromXY($this->nx, $this->ny);
    }

    public function layerData(): array
    {
        return $this->layerData;
    }

    public function layers(): array
    {
        return $this->layers;
    }

    public function affectedLayers(): AffectedLayers
    {
        return AffectedLayers::fromArray($this->layers);
    }

    public function to2DArray(): array
    {
        $cells = [];
        for ($iR=0; $iR<$this->ny; $iR++){
            $cells[$iR] = [];
            for ($iC=0; $iC<$this->nx; $iC++) {
                $cells[$iR][$iC] = false;
            }
        }

        foreach ($this->layerData as $row => $cols){
            foreach ($cols as $col => $value){
                $cells[(int)$row][(int)$col] = $value;
            }
        }

        return $cells;
    }

    public function toArray(): array
    {
        return array(
            'data' => $this->layerData,
            'n_x' => $this->nx,
            'n_y' => $this->ny,
            'layers' => $this->layers
        );
    }

    public function count(): int
    {
        return count($this->cells());
    }

    public function sameAs(ActiveCells $activeCells): bool
    {
        return $this->toArray() === $activeCells->toArray();
    }
}
