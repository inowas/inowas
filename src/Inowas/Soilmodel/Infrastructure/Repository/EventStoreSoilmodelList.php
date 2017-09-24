<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Infrastructure\Repository;

use Inowas\Soilmodel\Model\SoilmodelAggregate;
use Inowas\Common\Soilmodel\SoilmodelId;
use Inowas\Soilmodel\Model\SoilmodelList;
use Prooph\EventSourcing\Aggregate\AggregateRepository;

class EventStoreSoilmodelList extends AggregateRepository implements SoilmodelList
{

    public function save(SoilmodelAggregate $soilmodelAggregate)
    {
        $this->saveAggregateRoot($soilmodelAggregate);
    }

    public function get(SoilmodelId $soilmodelId)
    {
        return $this->getAggregateRoot($soilmodelId->toString());
    }
}
