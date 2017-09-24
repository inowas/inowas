<?php

namespace Inowas\Soilmodel\Infrastructure\Projection\LayerDetails;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Inowas\Common\Projection\AbstractDoctrineConnectionProjector;
use Inowas\Common\Soilmodel\BottomElevation;
use Inowas\Common\Soilmodel\HydraulicAnisotropy;
use Inowas\Common\Soilmodel\HydraulicConductivityX;
use Inowas\Common\Soilmodel\SpecificStorage;
use Inowas\Common\Soilmodel\SpecificYield;
use Inowas\Common\Soilmodel\TopElevation;
use Inowas\Common\Soilmodel\VerticalHydraulicConductivity;
use Inowas\Soilmodel\Model\Event\LayerPropertyWasUpdated;
use Inowas\Soilmodel\Model\Event\LayerValuesWereUpdated;
use Inowas\Soilmodel\Model\Event\SoilmodelGeologicalLayerWasAdded;
use Inowas\Soilmodel\Infrastructure\Projection\Table;
use Inowas\Soilmodel\Model\Event\SoilmodelWasCloned;

class LayerProjector extends AbstractDoctrineConnectionProjector
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);

        $schema = new Schema();
        $table = $schema->createTable(Table::LAYER_DETAILS);
        $table->addColumn('soilmodel_id', 'string', ['length' => 36]);
        $table->addColumn('layer_id', 'string', ['length' => 36]);
        $table->addColumn('layer_number', 'integer');
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('description', 'string', ['length' => 255]);
        $table->addColumn('laytyp', 'integer', ['notnull' => false]);
        $table->addColumn('layavg', 'integer', ['notnull' => false]);
        $table->addColumn('chani', 'float', ['notnull' => false]);
        $table->addColumn('layvka', 'integer', ['notnull' => false]);
        $table->addColumn('laywet', 'integer', ['notnull' => false]);
        $table->addColumn('top', 'text', ['notnull' => false]);
        $table->addColumn('botm', 'text', ['notnull' => false]);
        $table->addColumn('hk', 'text', ['notnull' => false]);
        $table->addColumn('hani', 'text', ['notnull' => false]);
        $table->addColumn('vka', 'text', ['notnull' => false]);
        $table->addColumn('ss', 'text', ['notnull' => false]);
        $table->addColumn('sy', 'text', ['notnull' => false]);
        $this->addSchema($schema);
    }

    public function onSoilmodelWasCloned(SoilmodelWasCloned $event): void
    {
        $layers = $this->connection->fetchAll(
            sprintf('SELECT * from %s WHERE soilmodel_id=:soilmodel_id', Table::LAYER_DETAILS),
            ['soilmodel_id' => $event->fromId()->toString()]
        );

        if ($layers === false) {
            return;
        }

        foreach ($layers as $layer){
            $this->connection->insert(Table::LAYER_DETAILS, array(
                'soilmodel_id' => $event->soilmodelId()->toString(),
                'layer_id' => $layer['layer_id'],
                'layer_number' => $layer['layer_number'],
                'name' => $layer['name'],
                'description' => $layer['description'],
                'laytyp' => $layer['laytyp'],
                'layavg' => $layer['layavg'],
                'chani' => $layer['chani'],
                'layvka' => $layer['layvka'],
                'laywet' => $layer['laywet'],
                'top' => $layer['top'],
                'botm' => $layer['botm'],
                'hk' => $layer['hk'],
                'hani' => $layer['hani'],
                'vka' => $layer['vka'],
                'ss' => $layer['ss'],
                'sy' => $layer['sy']
            ));
        }
    }

    public function onSoilmodelGeologicalLayerWasAdded(SoilmodelGeologicalLayerWasAdded $event): void
    {
        $this->connection->insert(Table::LAYER_DETAILS, array(
            'soilmodel_id' => $event->soilmodelId()->toString(),
            'layer_id' => $event->layer()->id()->toString(),
            'layer_number' => $event->layer()->layerNumber()->toInteger(),
            'laytyp' => $event->layer()->layerType()->toInt(),
            'name' => $event->layer()->layerName()->toString(),
            'description' => $event->layer()->layerDescription()->toString()
        ));
    }

    public function onLayerPropertyWasUpdated(LayerPropertyWasUpdated $event): void
    {
        $property = $event->property();
        if ($property instanceof BottomElevation) {
            $this->connection->update(Table::LAYER_DETAILS, array(
                'botm' => json_encode($property->toValue())
            ), array(
                    'soilmodel_id' => $event->soilmodelId()->toString(),
                    'layer_id' => $event->layerId()->toString())
            );
        }
        if ($property instanceof HydraulicAnisotropy) {
            $this->connection->update(Table::LAYER_DETAILS, array(
                'hani' => json_encode($property->toValue())
            ), array(
                    'soilmodel_id' => $event->soilmodelId()->toString(),
                    'layer_id' => $event->layerId()->toString())
            );
        }
        if ($property instanceof HydraulicConductivityX) {
            $this->connection->update(Table::LAYER_DETAILS, array(
                'hk' => json_encode($property->toValue())
            ), array(
                    'soilmodel_id' => $event->soilmodelId()->toString(),
                    'layer_id' => $event->layerId()->toString())
            );
        }
        if ($property instanceof SpecificStorage) {
            $this->connection->update(Table::LAYER_DETAILS, array(
                'ss' => json_encode($property->toValue())
            ), array(
                    'soilmodel_id' => $event->soilmodelId()->toString(),
                    'layer_id' => $event->layerId()->toString())
            );
        }
        if ($property instanceof SpecificYield) {
            $this->connection->update(Table::LAYER_DETAILS, array(
                'sy' => json_encode($property->toValue())
            ), array(
                    'soilmodel_id' => $event->soilmodelId()->toString(),
                    'layer_id' => $event->layerId()->toString())
            );
        }
        if ($property instanceof TopElevation) {
            $this->connection->update(Table::LAYER_DETAILS, array(
                'top' => json_encode($property->toValue())
            ), array(
                    'soilmodel_id' => $event->soilmodelId()->toString(),
                    'layer_id' => $event->layerId()->toString())
            );
        }
        if ($property instanceof VerticalHydraulicConductivity) {
            $this->connection->update(Table::LAYER_DETAILS, array(
                'vka' => json_encode($property->toValue())
            ), array(
                    'soilmodel_id' => $event->soilmodelId()->toString(),
                    'layer_id' => $event->layerId()->toString())
            );
        }
    }

    public function onLayerValuesWereUpdated(LayerValuesWereUpdated $event): void
    {
        $this->connection->update(Table::LAYER_DETAILS, array(
            'layavg' => $event->values()->layavg()->toInt(),
            'chani' => $event->values()->conductivity()->chani()->toValue(),
            'layvka' => $event->values()->conductivity()->layVka()->toValue(),
            'laywet' => $event->values()->laywet()->toValue(),
            'hk' => json_encode($event->values()->conductivity()->hk()->toValue()),
            'hani' => json_encode($event->values()->conductivity()->hani()->toValue()),
            'vka' => json_encode($event->values()->conductivity()->vka()->toValue()),
            'ss' => json_encode($event->values()->storage()->ss()->toValue()),
            'sy' => json_encode($event->values()->storage()->sy()->toValue()),
            'top' => json_encode($event->values()->hTop()),
            'botm' => json_encode($event->values()->hBottom()),
        ), array(
            'soilmodel_id' => $event->soilmodelId()->toString(),
            'layer_id' => $event->layerId()->toString())
        );
    }
}
