<?php

declare(strict_types=1);

namespace Inowas\Tool\Infrastructure\Repository;

use Inowas\Tool\Model\ToolId;
use Inowas\Tool\Model\ToolInstanceAggregate;
use Inowas\Tool\Model\ToolInstanceList;
use Prooph\EventSourcing\Aggregate\AggregateRepository;

class EventStoreToolInstanceList extends AggregateRepository implements ToolInstanceList
{
    /**
     * @param ToolInstanceAggregate $tool
     * @return void
     */
    public function save(ToolInstanceAggregate $tool): void
    {
        $this->saveAggregateRoot($tool);
    }

    /**
     * @param ToolId $id
     * @return mixed
     */
    public function get(ToolId $id)
    {
        return $this->getAggregateRoot($id->toString());
    }
}
