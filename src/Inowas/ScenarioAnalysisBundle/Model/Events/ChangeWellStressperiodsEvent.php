<?php

namespace Inowas\ScenarioAnalysisBundle\Model\Events;

use Inowas\ModflowBundle\Model\Boundary\WellBoundary;
use Inowas\ModflowBundle\Model\ModflowModel;
use Inowas\ScenarioAnalysisBundle\Model\Event;
use Ramsey\Uuid\Uuid;

class ChangeWellStressperiodsEvent extends Event
{
    public function __construct(Uuid $id, array $stressPeriods)
    {
        parent::__construct();
        $this->payload = [];
        $this->payload['id'] = $id->toString();
        $this->payload['stressPeriods'] = $stressPeriods;
        return $this;
    }

    /**
     * @param ModflowModel $model
     * @return void
     */
    public function applyTo(ModflowModel $model)
    {
        $id = $this->payload['id'];
        /** @var WellBoundary $boundary */
        $boundary = $model->getBoundary(Uuid::fromString($id));

        if ($boundary){
            $stressPeriods = $this->payload['stressPeriods'];
            $boundary->setStressPeriods($stressPeriods);
        }
    }
}