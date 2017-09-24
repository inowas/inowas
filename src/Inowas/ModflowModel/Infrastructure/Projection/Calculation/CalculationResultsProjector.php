<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Infrastructure\Projection\Calculation;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Inowas\Common\Calculation\CalculationState;
use Inowas\Common\Projection\AbstractDoctrineConnectionProjector;
use Inowas\ModflowModel\Infrastructure\Projection\Table;
use Inowas\ModflowModel\Model\Event\CalculationWasFinished;
use Inowas\ModflowModel\Model\Event\CalculationWasStarted;

class CalculationResultsProjector extends AbstractDoctrineConnectionProjector
{

    public function __construct(Connection $connection) {

        parent::__construct($connection);

        $schema = new Schema();
        $table = $schema->createTable(Table::CALCULATIONS);
        $table->addColumn('calculation_id', 'string', ['length' => 36, 'default' => '']);
        $table->addColumn('state', 'integer', ['default' => 0]);
        $table->addColumn('message', 'text', ['default' => '']);
        $table->addColumn('heads', 'text', ['default' => '[]']);
        $table->addColumn('budgets', 'text', ['default' => '[]']);
        $table->addColumn('drawdowns', 'text', ['default' => '[]']);
        $table->addColumn('number_of_layers', 'integer', ['default' => 0]);
        $table->setPrimaryKey(['calculation_id']);
        $this->addSchema($schema);
    }

    public function onCalculationWasStarted(CalculationWasStarted $event): void
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT count(*) from %s WHERE calculation_id = :calculation_id', Table::CALCULATIONS),
            ['calculation_id' => $event->calculationId()->toString()]
        );

        if ($result['count'] > 0){
            $this->connection->update(Table::CALCULATIONS,
                array('state' => CalculationState::started()->toInt()),
                array('calculation_id' => $event->calculationId()->toString())
            );

            return;
        }

        $this->connection->insert(Table::CALCULATIONS, array(
            'calculation_id' => $event->calculationId()->toString(),
            'state' => CalculationState::started()->toInt()
        ));
    }

    public function onCalculationWasFinished(CalculationWasFinished $event): void
    {
        $response = $event->response();

        $this->connection->update(Table::CALCULATIONS,
            array(
                'state' => CalculationState::finished()->toInt(),
                'message' => $response->message(),
                'heads' => json_encode($response->heads()),
                'budgets' => json_encode($response->budgets()),
                'drawdowns' => json_encode($response->drawdowns()),
                'number_of_layers' => $response->numberOfLayers()
            ), array(
                'calculation_id' => $event->calculationId()->toString()
            )
        );
    }

    public function resetNotFinishedCalculations(): void
    {
        $rows = $this->connection->fetchAll(
            sprintf('SELECT * from %s WHERE state = :state', Table::CALCULATIONS),
            ['state' => CalculationState::started()->toInt()]
        );

        foreach ($rows as $row) {
            $this->connection->update(Table::CALCULATIONS,
                ['state' => CalculationState::new()->toInt()],
                ['calculation_id' => $row['calculation_id']]
            );
        }
    }
}
