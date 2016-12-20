<?php

namespace Inowas\ScenarioAnalysisBundle\Model\Events;

use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Inowas\ModflowBundle\Model\Boundary\WellBoundary;
use Inowas\ModflowBundle\Model\BoundaryFactory;
use Inowas\ModflowBundle\Model\ModflowModel;
use Inowas\ModflowBundle\Model\StressPeriodFactory;
use Inowas\ScenarioAnalysisBundle\Model\Event;
use Ramsey\Uuid\Uuid;

class AddWellEvent extends Event
{
    /**
     * AddWellEvent constructor.
     * @param string $name
     * @param Point $point
     */
    public function __construct(string $name, Point $point)
    {
        parent::__construct();
        $this->payload = [];
        $this->payload['name'] = $name;
        $this->payload['lat'] = $point->getLatitude();
        $this->payload['lng'] = $point->getLongitude();
        $this->payload['srid'] = $point->getSrid();
        $this->payload['id'] = Uuid::uuid4()->toString();
        return $this;
    }

    /**
     * @param ModflowModel $model
     * @return void
     */
    public function applyTo(ModflowModel $model)
    {
        /** @var WellBoundary $well */
        $well = BoundaryFactory::createWel();
        $well->setId(Uuid::fromString($this->payload['id']));
        $well->setName($this->payload['name']);

        $point = new Point(1,2,3);
        $point->setLatitude($this->payload['lat']);
        $point->setLongitude($this->payload['lng']);
        $point->setSrid($this->payload['srid']);
        $well->setGeometry($point);

        $well->addStressPeriod(
            StressPeriodFactory::createWel()
                ->setDateTimeBegin(new \DateTime('2015-01-01'))
                ->setFlux(0)
        );
        $model->addBoundary($well);
    }
}