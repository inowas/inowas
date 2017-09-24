<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Infrastructure\Repository;

use Inowas\Common\Id\ModflowId;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;
use Prooph\EventSourcing\Aggregate\AggregateRepository;

class EventStoreModflowModelList extends AggregateRepository implements ModflowModelList
{
    /**
     * @param ModflowModelAggregate $model
     * @return void
     */
    public function save(ModflowModelAggregate $model): void
    {
        $this->saveAggregateRoot($model);
    }

    /**
     * @param ModflowId $modelId
     * @return mixed
     */
    public function get(ModflowId $modelId)
    {
        return $this->getAggregateRoot($modelId->toString());
    }
}
