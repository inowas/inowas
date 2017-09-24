<?php

namespace Inowas\ModflowModel\Infrastructure\Projection\Soilmodel;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Inowas\Common\Projection\AbstractDoctrineConnectionProjector;
use Inowas\Common\Soilmodel\Soilmodel;
use Inowas\ModflowModel\Infrastructure\Projection\Table;
use Inowas\ModflowModel\Model\Event\LayerWasAdded;
use Inowas\ModflowModel\Model\Event\LayerWasRemoved;
use Inowas\ModflowModel\Model\Event\LayerWasUpdated;
use Inowas\ModflowModel\Model\Event\ModflowModelWasCloned;
use Inowas\ModflowModel\Model\Event\ModflowModelWasCreated;
use Inowas\ModflowModel\Model\Event\SoilmodelMetadataWasUpdated;
use Inowas\ModflowModel\Model\Exception\SqlQueryException;

class SoilmodelProjector extends AbstractDoctrineConnectionProjector
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $schema = new Schema();
        $table = $schema->createTable(Table::SOILMODELS);
        $table->addColumn('model_id', 'string', ['length' => 36]);
        $table->addColumn('soilmodel', 'text', ['notnull' => false]);
        $table->setPrimaryKey(['model_id']);
        $this->addSchema($schema);

        $schema = new Schema();
        $table = $schema->createTable(Table::SOILMODEL_LAYERS_LIST);
        $table->addColumn('id', 'integer', array("unsigned" => true, "autoincrement" => true));
        $table->addColumn('model_id', 'string', ['length' => 36]);
        $table->addColumn('layer_id', 'string', ['length' => 36]);
        $table->addColumn('layer_number', 'integer');
        $table->addColumn('hash', 'text', ['notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['model_id', 'layer_id']);
        $this->addSchema($schema);
    }

    public function onModflowModelWasCreated(ModflowModelWasCreated $event): void
    {
        $this->connection->insert(Table::SOILMODELS, array(
            'model_id' => $event->modelId()->toString(),
            'soilmodel' => json_encode(Soilmodel::fromDefaults()->toArray())
        ));
    }

    public function onModflowModelWasCloned(ModflowModelWasCloned $event): void
    {

        /* Clone Metadata */
        $row = $this->connection->fetchAssoc(
            sprintf('SELECT soilmodel FROM %s WHERE model_id = :model_id', Table::SOILMODELS),
            [ 'model_id' => $event->baseModelId()->toString() ]
        );

        if ($row === false) {
            throw SqlQueryException::withClassName(__CLASS__, __FUNCTION__);
        }

        $this->connection->insert(Table::SOILMODELS, array(
            'model_id' => $event->modelId()->toString(),
            'soilmodel' => $row['soilmodel']
        ));

        /* Clone Layers */
        $rows = $this->connection->fetchAll(
            sprintf('SELECT layer_id, layer_number, hash FROM %s WHERE model_id = :model_id', Table::SOILMODEL_LAYERS_LIST),
            [ 'model_id' => $event->baseModelId()->toString() ]
        );

        if ($rows === false) {
            throw SqlQueryException::withClassName(__CLASS__, __FUNCTION__);
        }

        foreach ($rows as $row) {
            $this->connection->insert(Table::SOILMODEL_LAYERS_LIST, array(
                'model_id' => $event->modelId()->toString(),
                'layer_id' => $row['layer_id'],
                'layer_number' => $row['layer_number'],
                'hash' => $row['hash']
            ));
        }
    }

    public function onLayerWasAdded(LayerWasAdded $event): void
    {
        $this->connection->insert(Table::SOILMODEL_LAYERS_LIST, array(
            'model_id' => $event->modelId()->toString(),
            'layer_id' => $event->layerId()->toString(),
            'layer_number' => $event->layerNumber()->toInt(),
            'hash' => $event->hash()
        ));
    }

    public function onLayerWasUpdated(LayerWasUpdated $event): void
    {
        $this->connection->update(Table::SOILMODEL_LAYERS_LIST, array(
            'layer_id' => $event->layerId()->toString(),
            'layer_number' => $event->layerNumber()->toInt(),
            'hash' => $event->hash()
        ), array(
            'model_id' => $event->modelId()->toString(),
            'layer_id' => $event->layerId()->toString()
        ));
    }

    public function onLayerWasRemoved(LayerWasRemoved $event): void
    {
        $this->connection->delete(Table::SOILMODEL_LAYERS_LIST, array(
            'model_id' => $event->modelId()->toString(),
            'layer_id' => $event->layerId()->toString()
        ));
    }

    public function onSoilmodelMetadataWasUpdated(SoilmodelMetadataWasUpdated $event): void
    {
        $this->connection->update(Table::SOILMODELS, array(
            'soilmodel' => json_encode($event->soilmodel()->toArray()),
        ), array(
            'model_id' => $event->modelId()->toString()
        ));
    }
}
