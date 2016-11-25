<?php

namespace Inowas\Flopy\Model\Adapter;

use Inowas\ModflowBundle\Model\Boundary\RechargeBoundary;
use Inowas\ModflowBundle\Model\ModflowModel;

class RchPackageAdapter
{
    /**
     * @var ModFlowModel
     */
    private $model;

    /**
     * RchPackageAdapter constructor.
     * @param ModFlowModel $model
     */
    public function __construct(ModflowModel $model)
    {
        $this->model = $model;
    }

    /**
     * @return int
     */
    public function getIpakcb(): int
    {
        return 0;
    }

    /**
     * @return int
     */
    public function getNrchop(): int
    {
        return 3;
    }

    /**
     * @return array
     */
    public function getStressPeriodData(): array
    {
        $boundaries = array();
        foreach ($this->model->getBoundaries() as $boundary){
            if ($boundary instanceof RechargeBoundary){
                $boundaries[] = $boundary;
            }
        }

        $globalStressPeriods = $this->model->getGlobalStressPeriods();
        $stress_period_data = array();

        foreach ($globalStressPeriods->getTotalTimesStart() as $key => $startTime){
            /** @var RechargeBoundary $boundary */
            foreach ($boundaries as $boundary) {
                $data =  $boundary->getStressPeriodData($this->model->getStart(), $this->model->getTimeUnit(), $startTime);

                if (! is_null($data)){
                    if (! array_key_exists($key, $stress_period_data)){
                        $stress_period_data[$key] = array();
                    }

                    $stress_period_data[$key] = array_merge($stress_period_data[$key], $data);
                }
            }
        }

        return $stress_period_data;
    }

    /**
     * @return int
     */
    public function getIrch(): int
    {
        return 0;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return 'rch';
    }

    /**
     * @return int
     */
    public function getUnitnumber(): int
    {
        return 19;
    }
}