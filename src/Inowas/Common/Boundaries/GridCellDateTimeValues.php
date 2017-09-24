<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\DateTime\DateTime;

class GridCellDateTimeValues
{
    /** @var int  */
    private $layer;

    /** @var int  */
    private $row;

    /** @var int  */
    private $column;

    /** @var DateTimeValuesCollection */
    private $dateTimeValues;

    public static function fromParams(int $layer, int $row, int $column, DateTimeValuesCollection $dateTimeValues): GridCellDateTimeValues
    {
        return new self($layer, $row, $column, $dateTimeValues);
    }

    private function __construct(int $layer, int $row, int $column, DateTimeValuesCollection $dateTimeValues)
    {
        $this->layer = $layer;
        $this->row = $row;
        $this->column = $column;
        $this->dateTimeValues = $dateTimeValues;
    }

    public function layer(): int
    {
        return $this->layer;
    }

    public function row(): int
    {
        return $this->row;
    }

    public function column(): int
    {
        return $this->column;
    }

    public function dateTimeValues(): DateTimeValuesCollection
    {
        return $this->dateTimeValues;
    }

    public function findValueByDateTime(DateTime $dateTime): ?DateTimeValue
    {
        return $this->dateTimeValues->findValueByDateTime($dateTime);
    }
}
