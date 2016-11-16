<?php

namespace Inowas\ModflowBundle\Model\Adapter;

use Inowas\ModflowBundle\Model\Boundary\ConstantHeadBoundary;
use Inowas\ModflowBundle\Model\ModflowModel;

class ChdPackageAdapter
{

    /** @var  ModflowModel $model */
    protected $model;

    /**
     * ChdPackageAdapter constructor.
     * @param ModflowModel $model
     */
    public function __construct(ModflowModel $model){
        $this->model = $model;
    }

    /**
     * @return mixed
     */
    public function getStressPeriodData()
    {
        $boundaries = array();
        foreach ($this->model->getBoundaries() as $boundary){
            if ($boundary instanceof ConstantHeadBoundary){
                $boundaries[] = $boundary;
            }
        }

        $stress_period_data = array();
        /** @var ConstantHeadBoundary $boundary */
        foreach ($boundaries as $boundary) {
            $stress_period_data = $boundary->aggregateStressPeriodData($stress_period_data, $this->model->getStressPeriods());
        }

        return $stress_period_data;
    }

    /**
     * @return null
     */
    public function getDtype()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return 'chd';
    }

    /**
     * @return null
     */
    public function getOptions()
    {
        return null;
    }

    /**
     * @return int
     */
    public function getUnitnumber(): int
    {
        return 24;
    }
}
