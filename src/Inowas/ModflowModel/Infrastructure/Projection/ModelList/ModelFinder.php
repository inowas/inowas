<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Infrastructure\Projection\ModelList;

use Doctrine\DBAL\Connection;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Geometry\Polygon;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\CalculationId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\ModflowModel\Infrastructure\Projection\Table;

class ModelFinder
{
    /** @var Connection $connection */
    protected $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
        $this->connection->setFetchMode(\PDO::FETCH_OBJ);
    }

    public function findAll(): array
    {
        return $this->connection->fetchAll(
            sprintf('SELECT * FROM %s', Table::MODFLOWMODELS)
        );
    }

    public function findById(ModflowId $modelId): ?array
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT * FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );

        return $result === false ? null : $result;
    }

    public function findModelsByBaseUserId(UserId $userId): array
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $rows = $this->connection->fetchAll(
            sprintf('SELECT model_id AS id, user_id, user_name, name, description, area as geometry, grid_size, bounding_box, created_at, public FROM %s WHERE user_id = :user_id', Table::MODFLOWMODELS),
            ['user_id' => $userId->toString()]
        );

        foreach ($rows as $key => $row){
            $rows[$key]['geometry'] = json_decode($row['geometry'], true);
            $rows[$key]['grid_size'] = json_decode($row['grid_size'], true);
            $rows[$key]['bounding_box'] = json_decode($row['bounding_box'], true);
            $rows[$key]['public'] = (bool) $rows[$key]['public'];
        }

        return $rows;
    }

    public function findPublicModels(): array
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $rows = $this->connection->fetchAll(
            sprintf('SELECT model_id AS id, user_id, user_name, name, description, area as geometry, grid_size, bounding_box, created_at, public FROM %s WHERE public = :public', Table::MODFLOWMODELS),
            ['public' => 1]
        );

        foreach ($rows as $key => $row){
            $rows[$key]['geometry'] = json_decode($row['geometry'], true);
            $rows[$key]['grid_size'] = json_decode($row['grid_size'], true);
            $rows[$key]['bounding_box'] = json_decode($row['bounding_box'], true);
            $rows[$key]['public'] = (bool) $rows[$key]['public'];
        }

        return $rows;
    }

    public function getAreaGeometryByModflowModelId(ModflowId $modelId): ?Geometry
    {
        $result =  $this->connection->fetchAssoc(
            sprintf('SELECT area FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );


        if ($result === false){
            return null;
        }

        return Geometry::fromJson($result['area']);
    }

    public function getAreaPolygonByModflowModelId(ModflowId $modelId): ?Polygon
    {
        $geometry = $this->getAreaGeometryByModflowModelId($modelId);

        if (! $geometry instanceof Geometry){
            return null;
        }

        return $geometry->value();
    }

    public function getBoundingBoxByModflowModelId(ModflowId $modelId): ?BoundingBox
    {
        $result =  $this->connection->fetchAssoc(
            sprintf('SELECT bounding_box FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );

        if ($result === false){
            return null;
        }

        return BoundingBox::fromArray(json_decode($result['bounding_box'], true));
    }

    public function getCalculationIdByModelId(ModflowId $modelId): ?CalculationId
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT calculation_id FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );

        if ($result === false){
            return null;
        }

        return CalculationId::fromString($result['calculation_id']);
    }

    public function getGridSizeByModflowModelId(ModflowId $modelId): ?GridSize
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT grid_size FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );

        if ($result === false){
            return null;
        }

        return GridSize::fromArray((array)json_decode($result['grid_size']));
    }

    public function getLengthUnitByModelId(ModflowId $modelId): ?LengthUnit
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT length_unit FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );

        if ($result === false){
            return null;
        }

        return LengthUnit::fromInt($result['length_unit']);
    }

    public function getModelNameByModelId(ModflowId $modelId): ?Name
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT name FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );

        if ($result === false){
            return null;
        }

        return Name::fromString($result['name']);
    }

    public function getModelDescriptionByModelId(ModflowId $modelId): ?Description
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT description FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );

        if ($result === false){
            return null;
        }

        return Description::fromString($result['description']);
    }

    public function getModelDetailsByModelId(ModflowId $modelId): ?array
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT model_id AS id, user_id, user_name, name, description, area as geometry, length_unit, time_unit, grid_size, bounding_box, calculation_id, created_at, public FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );

        if ($result === false){
            return null;
        }

        $result['geometry'] = json_decode($result['geometry'], true);
        $result['grid_size'] = json_decode($result['grid_size'], true);
        $result['bounding_box'] = json_decode($result['bounding_box'], true);

        return $result;
    }

    public function getStressPeriodsByModelId(ModflowId $modelId): ?StressPeriods
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT stressperiods FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );

        if ($result === false){
            return null;
        }

        return StressPeriods::createFromJson($result['stressperiods']);
    }

    public function getTimeUnitByModelId(ModflowId $modelId): ?TimeUnit
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT time_unit FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );

        if ($result === false){
            return null;
        }

        return TimeUnit::fromInt($result['time_unit']);
    }

    public function userHasWriteAccessToModel(UserId $userId, ModflowId $modelId): bool
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT count(*) FROM %s WHERE model_id = :model_id AND user_id = :user_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString(), 'user_id' => $userId->toString()]
        );

        return $result['count'] > 0;
    }

    public function userHasReadAccessToModel(UserId $userId, ModflowId $modelId): bool
    {
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT count(*) FROM %s WHERE model_id = :model_id AND user_id = :user_id OR model_id = :model_id AND public = 1', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString(), 'user_id' => $userId->toString()]
        );

        return $result['count'] > 0;
    }

    public function modelExists(ModflowId $modelId): bool
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT count(*) FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $modelId->toString()]
        );

        return $result['count'] > 0;
    }
}
