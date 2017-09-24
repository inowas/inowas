<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Infrastructure\Repository;

use Inowas\Soilmodel\Model\BoreLogAggregate;
use Inowas\Common\Soilmodel\BoreLogId;
use Inowas\Soilmodel\Model\BoreLogList;
use Prooph\EventSourcing\Aggregate\AggregateRepository;

class EventStoreBoreLogList extends AggregateRepository implements BoreLogList
{

    public function save(BoreLogAggregate $boreLogAggregate)
    {
        $this->saveAggregateRoot($boreLogAggregate);
    }

    public function get(BoreLogId $boreLogId)
    {
        return $this->getAggregateRoot($boreLogId->toString());
    }
}
