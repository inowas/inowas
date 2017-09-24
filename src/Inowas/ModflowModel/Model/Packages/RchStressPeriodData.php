<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

class RchStressPeriodData extends AbstractStressPeriodData
{

    public static function create(): RchStressPeriodData
    {
        return new self();
    }

    public static function fromArray(array $data): RchStressPeriodData
    {
        $self = new self();
        $self->data = $data;
        return $self;
    }

    public function addStressPeriodValue(RchStressPeriodValue $rcpValue): RchStressPeriodData
    {
        $stressPeriod = $rcpValue->stressPeriod();
        $rech = $rcpValue->rech();

        if (!array_key_exists($stressPeriod, $this->data)) {
            $this->data[$stressPeriod] = $rech->toValue();
            return $this;
        }

        /** @var array $currentData */
        $currentData = $this->data[$stressPeriod];

        /** @var array $currentData */
        $newData = $rech->to2DArray();

        $data = [];

        /**
         * @var int $rowNr
         * @var array $row
         */
        foreach ($newData as $rowNr => $row) {
            $data[$rowNr] = [];
            foreach ($row as $colNr => $value) {

                $cdata = $currentData;
                if (is_array($currentData)) {
                    $cdata = $currentData[$rowNr][$colNr];
                }

                if ($newData[$rowNr][$colNr] > $cdata) {
                    $data[$rowNr][$colNr] = $newData[$rowNr][$colNr];
                } else {
                    $data[$rowNr][$colNr] = $cdata;
                }
            }
        }

        $this->data[$stressPeriod] = $data;
        return $this;
    }
}
