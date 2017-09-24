<?php

namespace Inowas\Soilmodel\Infrastructure\Projection\SoilmodelList;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Inowas\Common\Projection\AbstractDoctrineConnectionProjector;
use Inowas\Soilmodel\Model\Event\SoilmodelWasCloned;
use Inowas\Soilmodel\Model\Event\SoilmodelWasCreated;
use Inowas\Soilmodel\Infrastructure\Projection\Table;

class SoilmodelProjector extends AbstractDoctrineConnectionProjector
{

    public function __construct(Connection $connection)
    {

        parent::__construct($connection);

        $schema = new Schema();
        $table = $schema->createTable(Table::SOILMODEL_LIST);
        $table->addColumn('id', 'integer', array("unsigned" => true, "autoincrement" => true));
        $table->addColumn('user_id', 'string', ['length' => 36]);
        $table->addColumn('soilmodel_id', 'string', ['length' => 36]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('description', 'string', ['length' => 255]);
        $table->setPrimaryKey(['id']);
        $this->addSchema($schema);
    }

    public function onSoilmodelWasCreated(SoilmodelWasCreated $event): void
    {
        $this->connection->insert(Table::SOILMODEL_LIST, array(
            'user_id' => $event->userId()->toString(),
            'soilmodel_id' => $event->soilmodelId()->toString(),
            'name' => '',
            'description' => ''
        ));
    }

    public function onSoilmodelWasCloned(SoilmodelWasCloned $event): void
    {
        $this->connection->insert(Table::SOILMODEL_LIST, array(
            'user_id' => $event->userId()->toString(),
            'soilmodel_id' => $event->soilmodelId()->toString(),
            'name' => $event->name()->toString(),
            'description' => $event->description()->toString()
        ));
    }
}
