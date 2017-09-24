<?php

namespace Tests\Inowas\Common\Grid;

use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Grid\GridSize;

class ActiveCellsTest extends \PHPUnit_Framework_TestCase
{

    public function test_from_full_array_and_gridsize(): void
    {
        $arr = [
            [0,2,3],
            [4,0,6],
            [5,0,4],
            [6,9,0],
        ];

        $expectedArr = [
            [0,1,1],
            [1,0,1],
            [1,0,1],
            [1,1,0],
        ];

        $gridSize = GridSize::fromXY(3, 4);
        $activeCells = ActiveCells::fromArrayAndGridSize($arr, $gridSize);
        $this->assertInstanceOf(ActiveCells::class, $activeCells);
        $this->assertEquals($expectedArr, $activeCells->layerData());
        $this->assertEquals($expectedArr, $activeCells->to2DArray());
    }

    public function test_from_not_full_array_and_gridsize(): void
    {
        $arr = [];
        $arr[0][0] = 1;
        $arr[1][1] = 1;
        $arr[2][2] = 1;
        $arr[3][2] = 1;

        $fullArr = [
            [1,0,0],
            [0,1,0],
            [0,0,1],
            [0,0,1]
        ];

        $gridSize = GridSize::fromXY(3, 4);
        $activeCells = ActiveCells::fromArrayAndGridSize($arr, $gridSize);
        $this->assertInstanceOf(ActiveCells::class, $activeCells);
        $this->assertEquals($arr, $activeCells->layerData());
        $this->assertEquals($fullArr, $activeCells->to2DArray());
    }

    public function test_to_cells(): void
    {
        $arr = [];
        $arr[0][0] = 1;
        $arr[1][1] = 1;
        $arr[2][2] = 1;
        $arr[3][2] = 1;

        $gridSize = GridSize::fromXY(3, 4);
        $activeCells = ActiveCells::fromArrayAndGridSize($arr, $gridSize);
        $this->assertInstanceOf(ActiveCells::class, $activeCells);
        $this->assertEquals($arr, $activeCells->layerData());
        $this->assertCount(4, $arr);
    }

    public function test_from_cells(): void
    {
        $json = '[[4,43,28]]';
        $arr = json_decode($json);
        $activeCells = ActiveCells::fromCells($arr);
        $this->assertEquals($json, json_encode($activeCells->cells()));
    }
}
