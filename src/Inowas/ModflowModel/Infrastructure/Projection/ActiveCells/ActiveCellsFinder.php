<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Infrastructure\Projection\ActiveCells;

use Doctrine\DBAL\Connection;
use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ModflowId;
use Inowas\ModflowModel\Infrastructure\Projection\Table;

class ActiveCellsFinder
{

    /** @var Connection $connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->setFetchMode(\PDO::FETCH_OBJ);
    }

    public function findAreaActiveCells(ModflowId $modelId): ?ActiveCells
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT active_cells FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );

        if (null === $result['active_cells']){
            return null;
        }

        return ActiveCells::fromArray(json_decode($result['active_cells'], true));
    }

    public function updateAreaActiveCells(ModflowId $modelId, ActiveCells $activeCells): void
    {
        $this->connection->update(Table::MODFLOWMODELS, array(
            'active_cells' => json_encode($activeCells->toArray())
        ), array(
            'model_id' => $modelId->toString()
        ));
    }

    public function findBoundaryActiveCells(ModflowId $modelId, BoundaryId $boundaryId): ?ActiveCells
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT active_cells FROM %s WHERE boundary_id =:boundary_id AND model_id = :model_id', Table::BOUNDARIES),
            ['model_id' => $modelId->toString(), 'boundary_id' => $boundaryId->toString()]
        );

        if (null === $result['active_cells']){
            return null;
        }

        return ActiveCells::fromArray(json_decode($result['active_cells'], true));
    }

    public function updateBoundaryActiveCells(ModflowId $modelId, BoundaryId $boundaryId, ActiveCells $activeCells): void
    {
        $this->connection->update(Table::BOUNDARIES, array(
            'active_cells' => json_encode($activeCells->toArray())
        ), array(
            'model_id' => $modelId->toString(),
            'boundary_id' => $boundaryId->toString(),
        ));
    }
}
