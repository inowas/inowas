<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Infrastructure\Projection\ModelList;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Inowas\AppBundle\Model\User;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Common\Projection\AbstractDoctrineConnectionProjector;
use Inowas\ModflowModel\Model\Event\AreaGeometryWasUpdated;
use Inowas\ModflowModel\Model\Event\BoundaryWasAdded;
use Inowas\ModflowModel\Model\Event\BoundaryWasRemoved;
use Inowas\ModflowModel\Model\Event\BoundaryWasUpdated;
use Inowas\ModflowModel\Model\Event\BoundingBoxWasChanged;
use Inowas\ModflowModel\Model\Event\CalculationIdWasChanged;
use Inowas\ModflowModel\Model\Event\CalculationWasRequested;
use Inowas\ModflowModel\Model\Event\DescriptionWasChanged;
use Inowas\ModflowModel\Model\Event\GridSizeWasChanged;
use Inowas\ModflowModel\Model\Event\LengthUnitWasUpdated;
use Inowas\ModflowModel\Model\Event\ModflowModelWasCloned;
use Inowas\ModflowModel\Model\Event\ModflowModelWasDeleted;
use Inowas\ModflowModel\Model\Event\NameWasChanged;
use Inowas\ModflowModel\Model\Event\ModflowModelWasCreated;
use Inowas\ModflowModel\Infrastructure\Projection\Table;
use Inowas\ModflowModel\Model\Event\StressPeriodsWereUpdated;
use Inowas\ModflowModel\Model\Event\TimeUnitWasUpdated;
use Inowas\ModflowModel\Model\Event\VisibilityWasChanged;

class ModelProjector extends AbstractDoctrineConnectionProjector
{
    /** @var  EntityManager $entityManager */
    protected $entityManager;

    public function __construct(Connection $connection, EntityManager $entityManager)
    {
        parent::__construct($connection);
        $this->entityManager = $entityManager;

        $schema = new Schema();
        $table = $schema->createTable(Table::MODFLOWMODELS);
        $table->addColumn('model_id', 'string', ['length' => 36]);
        $table->addColumn('user_id', 'string', ['length' => 36]);
        $table->addColumn('user_name', 'string', ['length' => 255, 'default' => '']);
        $table->addColumn('name', 'string', ['length' => 255, 'default' => '']);
        $table->addColumn('description', 'string', ['length' => 255, 'default' => '']);
        $table->addColumn('area', 'text', ['notnull' => false]);
        $table->addColumn('grid_size', 'text', ['notnull' => false]);
        $table->addColumn('bounding_box', 'text', ['notnull' => false]);
        $table->addColumn('active_cells', 'text', ['notnull' => false]);
        $table->addColumn('time_unit', 'integer');
        $table->addColumn('length_unit', 'integer');
        $table->addColumn('stressperiods', 'text', ['notnull' => false]);
        $table->addColumn('calculation_id', 'string', ['length' => 36, 'notnull' => false]);
        $table->addColumn('dirty', 'smallint', ['default' => 0]);
        $table->addColumn('preprocessing', 'smallint', ['default' => 0]);
        $table->addColumn('created_at', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('public', 'smallint', ['default' => 1]);
        $table->setPrimaryKey(['model_id']);
        $table->addIndex(['calculation_id']);
        $this->addSchema($schema);
    }

    public function onAreaGeometryWasUpdated(AreaGeometryWasUpdated $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS, array(
            'area' => $event->geometry()->toJson(),
            'active_cells' => null,
            'dirty' => 1,
        ),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onBoundaryWasAdded(BoundaryWasAdded $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS, array(
            'dirty' => 1,
        ),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onBoundaryWasRemoved(BoundaryWasRemoved $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS, array(
            'dirty' => 1,
        ),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onBoundaryWasUpdated(BoundaryWasUpdated $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS, array(
            'dirty' => 1,
        ),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onBoundingBoxWasChanged(BoundingBoxWasChanged $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS, array(
            'bounding_box' => json_encode($event->boundingBox()),
            'active_cells' => null,
            'dirty' => 1,
        ),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onCalculationIdWasChanged(CalculationIdWasChanged $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS, array(
            'calculation_id' => $event->calculationId()->toString(),
            'dirty' => 0,
            'preprocessing' => 0
        ),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onCalculationWasRequested(CalculationWasRequested $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS,
            array('preprocessing' => 1),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onDescriptionWasChanged(DescriptionWasChanged $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS,
            array('description' => $event->description()->toString()),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onGridSizeWasChanged(GridSizeWasChanged $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS, array(
            'grid_size' => json_encode($event->gridSize()),
            'active_cells' => null,
            'dirty' => 1,
        ),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onLengthUnitWasUpdated(LengthUnitWasUpdated $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS,
            array(
                'length_unit' => $event->lengthUnit()->toInt(),
                'dirty' => 1,
            ),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onModflowModelWasCreated(ModflowModelWasCreated $event): void
    {
        $defaultTimeUnit = TimeUnit::fromInt(TimeUnit::DAYS);
        $defaultLengthUnit = LengthUnit::fromInt(LengthUnit::METERS);

        $this->connection->insert(Table::MODFLOWMODELS, array(
            'model_id' => $event->modelId()->toString(),
            'user_id' => $event->userId()->toString(),
            'user_name' => $this->getUserNameByUserId($event->userId()->toString()),
            'area' => $event->polygon()->toJson(),
            'grid_size' => json_encode($event->gridSize()),
            'bounding_box' => json_encode($event->boundingBox()),
            'active_cells' => null,
            'time_unit' => $defaultTimeUnit->toInt(),
            'length_unit' => $defaultLengthUnit->toInt(),
            'stressperiods' => StressPeriods::createDefault()->toJson(),
            'created_at' => date_format($event->createdAt(), DATE_ATOM),
            'public' => 1
        ));
    }

    public function onModflowModelWasCloned(ModflowModelWasCloned $event): void
    {
        $rows = $this->connection->fetchAll(
            sprintf('SELECT * FROM %s WHERE model_id = :model_id', Table::MODFLOWMODELS),
            ['model_id' => $event->baseModelId()->toString()]
        );

        foreach ($rows as $row){
            $this->connection->insert(Table::MODFLOWMODELS, array(
                'model_id' => $event->modelId()->toString(),
                'user_id' => $event->userId()->toString(),
                'user_name' => $this->getUserNameByUserId($event->userId()->toString()),
                'name' => $row['name'],
                'description' => $row['description'],
                'area' => $row['area'],
                'grid_size' => $row['grid_size'],
                'bounding_box' => $row['bounding_box'],
                'active_cells' => $row['active_cells'],
                'time_unit' => $row['time_unit'],
                'length_unit' => $row['length_unit'],
                'stressperiods' => $row['stressperiods'],
                'calculation_id' => $row['calculation_id'],
                'dirty' => $row['dirty'],
                'created_at' => date_format($event->createdAt(), DATE_ATOM),
                'public' => 1
            ));
        }
    }

    public function onModflowModelWasDeleted(ModflowModelWasDeleted $event): void
    {
        $this->connection->delete(Table::MODFLOWMODELS, array(
            'model_id' => $event->modelId()->toString(),
            'user_id' => $event->userId()->toString()
        ));
    }

    public function onNameWasChanged(NameWasChanged $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS,
            array('name' => $event->name()->toString()),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onTimeUnitWasUpdated(TimeUnitWasUpdated $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS,
            array(
                'time_unit' => $event->timeUnit()->toInt(),
                'dirty' => 1,
            ),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onStressPeriodsWereUpdated(StressPeriodsWereUpdated $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS,
            array(
                'stressperiods' => $event->stressPeriods()->toJson(),
                'dirty' => 1,
            ),
            array('model_id' => $event->modelId()->toString())
        );
    }

    public function onVisibilityWasChanged(VisibilityWasChanged $event): void
    {
        $this->connection->update(Table::MODFLOWMODELS,
            array(
                'public' => $event->visibility()->isPublic() ? 1 : 0
            ),
            array('model_id' => $event->modelId()->toString())
        );
    }

    private function getUserNameByUserId(string $id): string
    {
        $username = '';
        $user = $this->entityManager->getRepository('InowasAppBundle:User')->findOneBy(array('id' => $id));
        if ($user instanceof User){
            $username = $user->getName();
        }

        return $username;
    }
}
