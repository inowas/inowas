<?php

namespace AppBundle\Entity;

use AppBundle\Model\ActiveCells;
use AppBundle\Model\BoundaryInterface;
use AppBundle\Model\StressPeriod;
use Doctrine\Common\Collections\ArrayCollection;

abstract class BoundaryModelObject extends ModelObject implements BoundaryInterface
{
    /**
     * @param StressPeriod $stressPeriod
     * @param ArrayCollection $globalStressPeriods
     * @return int|mixed|null|string
     */
    protected function getGlobalStressPeriodKey(StressPeriod $stressPeriod, ArrayCollection $globalStressPeriods){
        $key = null;
        foreach ($globalStressPeriods as $key => $globalStressPeriod){
            if ($stressPeriod->getDateTimeBegin() == $globalStressPeriod->getDateTimeBegin()){
                return $key;
            }
        }

        return $key;
    }

    /**
     * @param array $stressPeriodData
     * @param ArrayCollection $globalStressPeriods
     * @return array
     */
    public function aggregateStressPeriodData(array $stressPeriodData, ArrayCollection $globalStressPeriods){

        /** @var StressPeriod $stressPeriod */
        foreach ($this->getStressPeriods() as $stressPeriod) {
            $globalStressPeriodsKey = $this->getGlobalStressPeriodKey($stressPeriod, $globalStressPeriods);

            if (is_null($globalStressPeriodsKey)) {
                continue;
            }

            $data = $this->generateStressPeriodData($stressPeriod, $this->activeCells);

            if (!isset($stressPeriodData[$globalStressPeriodsKey])) {
                $stressPeriodData[$globalStressPeriodsKey] = $this->generateStressPeriodData($stressPeriod, $this->activeCells);
                continue;
            }

            if (is_array($data)){
                $stressPeriodData[$globalStressPeriodsKey] = $this->mergeArrays($stressPeriodData[$globalStressPeriodsKey], $data);
            } else {
                $stressPeriodData[$globalStressPeriodsKey] = $data;
            }
        }

        return $stressPeriodData;
    }

    /**
     * @param array $current
     * @param array $new
     * @return array
     */
    protected function mergeArrays(array $current, array $new){
        $merged = array_merge($current, $new);
        return $merged;
    }

    /**
     * @param StressPeriod $stressPeriod
     * @param ActiveCells $activeCells
     * @return mixed
     */
    abstract protected function generateStressPeriodData(StressPeriod $stressPeriod, ActiveCells $activeCells);
}