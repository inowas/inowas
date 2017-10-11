<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Infrastructure\Projection\BoundaryList;

use Doctrine\DBAL\Connection;
use Inowas\Common\Boundaries\BoundaryFactory;
use Inowas\Common\Boundaries\BoundaryList;
use Inowas\Common\Boundaries\BoundaryListItem;
use Inowas\Common\Boundaries\BoundaryType;
use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\ModflowBoundary;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Modflow\Name;
use Inowas\ModflowModel\Infrastructure\Projection\Table;
use Inowas\ModflowModel\Model\Exception\SqlQueryException;

class BoundaryFinder
{
    /** @var Connection $connection */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
        $this->connection->setFetchMode(\PDO::FETCH_OBJ);
    }

    public function getTotalNumberOfModelBoundaries(ModflowId $modelId): int
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT count(*) FROM %s WHERE model_id = :model_id', Table::BOUNDARIES),
            ['model_id' => $modelId->toString()]
        );

        if ($result === false) {
            throw SqlQueryException::withClassName(__CLASS__, __FUNCTION__);
        }

        return (int)$result['count'];
    }

    public function getNumberOfModelBoundariesByType(ModflowId $modelId, BoundaryType $type): int
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT count(*) FROM %s WHERE model_id = :model_id AND type = :type', Table::BOUNDARIES),
            ['model_id' => $modelId->toString(), 'type' => $type->toString()]
        );

        if ($result === false) {
            throw SqlQueryException::withClassName(__CLASS__, __FUNCTION__);
        }

        return (int)$result['count'];
    }

    public function findConstantHeadBoundaries(ModflowId $modelId): array
    {
        return $this->getBoundariesByModelIdAndType($modelId, BoundaryType::fromString(BoundaryType::CONSTANT_HEAD));
    }

    public function findGeneralHeadBoundaries(ModflowId $modelId): array
    {
        return $this->getBoundariesByModelIdAndType($modelId, BoundaryType::fromString(BoundaryType::GENERAL_HEAD));
    }

    public function findRechargeBoundaries(ModflowId $modelId): array
    {
        return $this->getBoundariesByModelIdAndType($modelId, BoundaryType::fromString(BoundaryType::RECHARGE));
    }

    public function findRiverBoundaries(ModflowId $modelId): array
    {
        return $this->getBoundariesByModelIdAndType($modelId, BoundaryType::fromString(BoundaryType::RIVER));
    }

    public function findWellBoundaries(ModflowId $modelId): array
    {
        return $this->getBoundariesByModelIdAndType($modelId, BoundaryType::fromString(BoundaryType::WELL));
    }

    public function findBoundariesByModelId(ModflowId $modelId): ?array
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);

        $result = $this->connection->fetchAll(
            sprintf('SELECT boundary_id as id, name, type, geometry, metadata, affected_layers FROM %s WHERE model_id = :model_id', Table::BOUNDARIES),
            ['model_id' => $modelId->toString()]
        );

        if (false === $result) {
            return null;
        }

        return $result;
    }

    public function getBoundary(ModflowId $modelId, BoundaryId $boundaryId): ?ModflowBoundary
    {
        $row = $this->connection->fetchAssoc(
            sprintf('SELECT boundary_id AS id, boundary FROM %s WHERE model_id = :model_id AND boundary_id = :boundary_id', Table::BOUNDARIES),
            ['model_id' => $modelId->toString(), 'boundary_id' => $boundaryId->toString()]
        );

        if ($row === false){
            return null;
        }

        return BoundaryFactory::createFromArray(json_decode($row['boundary'], true));
    }

    public function getBoundaryName(ModflowId $modelId, BoundaryId $boundaryId): ?Name
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT name FROM %s WHERE model_id = :model_id AND boundary_id = :boundary_id', Table::BOUNDARIES),
            ['model_id' => $modelId->toString(), 'boundary_id' => $boundaryId->toString()]
        );

        if ($result === false){
            return null;
        }

        return Name::fromString($result['name']);
    }

    public function getBoundaryGeometry(ModflowId $modelId, BoundaryId $boundaryId): ?Geometry
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT geometry FROM %s WHERE boundary_id =:boundary_id AND model_id = :model_id', Table::BOUNDARIES),
            ['model_id' => $modelId->toString(), 'boundary_id' => $boundaryId->toString()]
        );

        if ($result === false){
            return null;
        }

        return Geometry::fromArray(json_decode($result['geometry'], true));
    }

    public function getBoundaryType(ModflowId $modelId, BoundaryId $boundaryId): ?BoundaryType
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT type FROM %s WHERE model_id = :model_id AND boundary_id = :boundary_id', Table::BOUNDARIES),
            ['model_id' => $modelId->toString(), 'boundary_id' => $boundaryId->toString()]
        );

        if ($result === false){
            return null;
        }

        return BoundaryType::fromString($result['type']);
    }

    public function findStressPeriodDatesById(ModflowId $modelId): array
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $rows = $this->connection->fetchAll(
            sprintf('SELECT boundary FROM %s WHERE model_id = :model_id', Table::BOUNDARIES),
            ['model_id' => $modelId->toString()]
        );

        if ($rows === false) {
            throw SqlQueryException::withClassName(__CLASS__, __FUNCTION__);
        }

        $spDates = [];
        foreach ($rows as $row) {
            /** @var ModflowBoundary $boundary */
            $boundary = BoundaryFactory::createFromArray(json_decode($row['boundary'], true));
            $dateTimes = $boundary->getDateTimes();

            /** @var DateTime $dateTime */
            foreach ($dateTimes as $dateTime) {
                if (! in_array($dateTime->toAtom(), $spDates, true)) {
                    $spDates[] = $dateTime;
                }
            }
        }

        sort($spDates);
        return $spDates;
    }

    public function getAffectedLayersByModelAndBoundary(ModflowId $modelId, BoundaryId $boundaryId): AffectedLayers
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT * FROM %s WHERE model_id = :model_id AND boundary_id = :boundary_id', Table::BOUNDARIES),
            ['model_id' => $modelId->toString(), 'boundary_id' => $boundaryId->toString()]
        );

        return AffectedLayers::fromArray(json_decode($result['affected_layers'], true));
    }

    private function getBoundariesByModelIdAndType(ModflowId $modelId, BoundaryType $type): array
    {
        $rows = $this->connection->fetchAll(
            sprintf('SELECT boundary FROM %s WHERE model_id = :model_id AND type = :type', Table::BOUNDARIES),
            ['model_id' => $modelId->toString(), 'type' => $type->toString()]
        );

        $result = [];
        foreach ($rows as $row) {
            $result[] = BoundaryFactory::createFromArray(json_decode($row['boundary'], true));
        }

        return $result;
    }

    public function getBoundaryList(ModflowId $modelId): BoundaryList
    {
        $boundaryList = BoundaryList::create();

        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $rows = $this->connection->fetchAll(
            sprintf('SELECT boundary_id, name, geometry, type, metadata, affected_layers FROM %s WHERE model_id = :model_id', Table::BOUNDARIES),
            ['model_id' => $modelId->toString()]
        );

        if ($rows === false){
            return $boundaryList;
        }

        foreach ($rows as $row) {
            $boundaryList = $boundaryList->addItem(
                BoundaryListItem::fromParams(
                    BoundaryId::fromString($row['boundary_id']),
                    Name::fromString($row['name']),
                    Geometry::fromJson($row['geometry']),
                    BoundaryType::fromString($row['type']),
                    Metadata::fromArray(json_decode($row['metadata'], true)),
                    AffectedLayers::fromArray(json_decode($row['affected_layers'], true))
                )
            );
        }

        return $boundaryList;
    }
}
