<?php

declare(strict_types=1);

namespace Inowas\Modflow\Model\Packages;

class WelStressPeriodData implements \JsonSerializable
{
    /** @var array */
    protected $data = array();

    public static function create(): WelStressPeriodData
    {
        return new self();
    }

    public static function fromArray(array $data): WelStressPeriodData
    {
        $self = new self();
        $self->data = $data;
        return $self;
    }

    public function addGridCellValue(WelStressPeriodGridCellValue $gridCellValue): WelStressPeriodData
    {
        $stressPeriod = $gridCellValue->stressPeriod();
        $layer = $gridCellValue->lay();
        $row = $gridCellValue->row();
        $column = $gridCellValue->col();
        $value = $gridCellValue->value();

        if (! is_array($this->data)){
            $this->data = array();
        }

        if (! array_key_exists($stressPeriod, $this->data)){
            $this->data[$stressPeriod] = array();
        }

        // This checks if there are yet cells with value and aggregates them
        foreach ($this->data[$stressPeriod] as &$data) {
            if ($data[0] == $layer && $data[1] == $row && $data[2] == $column){
                $data[3] += $value;
                return $this;
            }
        }

        $this->data[$stressPeriod][] = [$layer, $row, $column, $value];
        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function jsonSerialize(): array
    {
        return array(
            "stress_period_data" => $this->data
        );
    }
}