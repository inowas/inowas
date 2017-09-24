<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

abstract class AbstractStressPeriodData implements \JsonSerializable
{
    /** @var array */
    protected $data = [];

    abstract public static function create();

    abstract public static function fromArray(array $data);

    public function toArray(): array
    {
        return $this->data;
    }

    public function hasData(): bool
    {
        return count($this->data) > 0;
    }

    public function jsonSerialize(): array
    {
        $this->removeSuccessiveStressperiodData();

        return array(
            'stress_period_data' => (object)$this->data
        );
    }

    protected function removeSuccessiveStressperiodData(): void
    {
        $data = [];
        foreach ($this->data as $key => $spData) {
            if ($key === 0){
                $data[$key] = $spData;
                continue;
            }

            if ($this->data[$key-1] !== $this->data[$key]) {
                $data[$key] = $spData;
            }
        }

        $this->data = $data;
    }
}
