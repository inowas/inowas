<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Infrastructure\Projection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Inowas\Common\Projection\AbstractDoctrineConnectionProjector;
use Inowas\ModflowModel\Infrastructure\Projection\ModelList\ModelFinder;
use Inowas\ModflowModel\Model\Event\CalculationIdWasChanged;
use Inowas\ModflowModel\Model\Event\DescriptionWasChanged;
use Inowas\ModflowModel\Model\Event\NameWasChanged;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasCloned;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasCreated;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasDeleted;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioWasCreated;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioWasDeleted;

class ScenarioListProjector extends AbstractDoctrineConnectionProjector
{

    /** @var  ModelFinder */
    private $modelFinder;

    public function __construct(Connection $connection, ModelFinder $modelFinder)
    {
        $this->modelFinder = $modelFinder;

        parent::__construct($connection);

        $schema = new Schema();
        $table = $schema->createTable(Table::SCENARIO_LIST);
        $table->addColumn('id', 'integer')->setAutoincrement(true);
        $table->addColumn('scenario_id', 'string', ['length' => 36]);
        $table->addColumn('base_model_id', 'string', ['length' => 36]);
        $table->addColumn('scenario_analysis_id', 'string', ['length' => 36]);
        $table->addColumn('calculation_id', 'string', ['length' => 36, 'default' => false]);
        $table->addColumn('user_id', 'string', ['length' => 36]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('description', 'string', ['length' => 255]);
        $table->addColumn('is_base_model', 'boolean', ['default' => false]);
        $table->addColumn('is_scenario', 'boolean', ['default' => false]);
        $table->addColumn('created_at', 'string', ['length' => 255, 'notnull' => false]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(array('scenario_analysis_id'));
        $table->addIndex(array('base_model_id'));
        $table->addIndex(array('scenario_id'));
        $this->addSchema($schema);
    }

    public function onNameWasChanged(NameWasChanged $event): void
    {
        $this->connection->update(Table::SCENARIO_LIST, array(
            'name' => $event->name()->toString()
        ), array(
            'scenario_id' => $event->modelId()->toString()
        ));
    }

    public function onDescriptionWasChanged(DescriptionWasChanged $event): void
    {
        $this->connection->update(Table::SCENARIO_LIST, array(
            'description' => $event->description()->toString()
        ), array(
            'scenario_id' => $event->modelId()->toString()
        ));
    }

    public function onScenarioAnalysisWasCreated(ScenarioAnalysisWasCreated $event): void
    {
        $baseModelName = $this->modelFinder->getModelNameByModelId($event->baseModelId());
        $baseModelDescription = $this->modelFinder->getModelDescriptionByModelId($event->baseModelId());
        $calculationId = $this->modelFinder->getCalculationIdByModelId($event->baseModelId());

        if (null === $baseModelName || null === $baseModelDescription || null === $calculationId) {
            return;
        }

        $this->connection->insert(Table::SCENARIO_LIST, array(
            'scenario_id' => $event->baseModelId()->toString(),
            'base_model_id' => $event->baseModelId()->toString(),
            'scenario_analysis_id' => $event->scenarioAnalysisId()->toString(),
            'user_id' => $event->userId()->toString(),
            'name' => $baseModelName->toString(),
            'description' => $baseModelDescription->toString(),
            'calculation_id' => $calculationId->toString(),
            'is_base_model' => 1,
            'is_scenario' => 0,
            'created_at' => date_format($event->createdAt(), DATE_ATOM),
        ));
    }

    public function onScenarioAnalysisWasCloned(ScenarioAnalysisWasCloned $event): void
    {
        $rows = $this->connection->fetchAll(
            sprintf('SELECT * FROM %s WHERE scenario_analysis_id=:scenario_analysis_id AND is_base_model = TRUE', Table::SCENARIO_LIST),
            ['scenario_analysis_id' => $event->fromScenarioAnalysisId()->toString()]
        );

        if (false === $rows){
            return;
        }

        foreach ($rows as $row){
            $this->connection->insert(Table::SCENARIO_LIST, array(
                'scenario_id' => $event->baseModelId()->toString(),
                'base_model_id' => $event->baseModelId()->toString(),
                'scenario_analysis_id' => $event->scenarioAnalysisId()->toString(),
                'user_id' => $event->userId()->toString(),
                'name' => $row['name'],
                'description' => $row['description'],
                'calculation_id' => $row['calculation_id'],
                'is_base_model' => 1,
                'is_scenario' => 0,
                'created_at' => date_format($event->createdAt(), DATE_ATOM),
            ));
        }

        $rows = $this->connection->fetchAll(
            sprintf('SELECT * FROM %s WHERE scenario_analysis_id=:scenario_analysis_id AND is_scenario = TRUE', Table::SCENARIO_LIST),
            ['scenario_analysis_id' => $event->fromScenarioAnalysisId()->toString()]
        );

        if (false === $rows){
            return;
        }

        foreach ($rows as $key => $row){
            $this->connection->insert(Table::SCENARIO_LIST, array(
                'scenario_id' => $event->scenarios()[$key],
                'base_model_id' => $event->baseModelId()->toString(),
                'scenario_analysis_id' => $event->scenarioAnalysisId()->toString(),
                'user_id' => $event->userId()->toString(),
                'name' => $row['name'],
                'description' => $row['description'],
                'calculation_id' => $row['calculation_id'],
                'is_base_model' => 0,
                'is_scenario' => 1,
                'created_at' => date_format($event->createdAt(), DATE_ATOM),
            ));
        }
    }

    public function onScenarioAnalysisWasDeleted(ScenarioAnalysisWasDeleted $event): void
    {
        $this->connection->delete(
            Table::SCENARIO_LIST,
            ['scenario_analysis_id' => $event->scenarioAnalysisId()->toString()]
        );
    }

    public function onScenarioWasCreated(ScenarioWasCreated $event): void
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT calculation_id, name, description from %s WHERE scenario_id = :scenario_id', Table::SCENARIO_LIST),
            array(
                'scenarioanalysis_id' => $event->scenarioAnalysisId()->toString(),
                'scenario_id' => $event->baseModelId()->toString()
            )
        );

        if (false === $result) {
            return;
        }

        $calculationId = '';
        if (array_key_exists('calculation_id', $result)){
            $calculationId = $result['calculation_id'];
        }

        $this->connection->insert(Table::SCENARIO_LIST, array(
            'scenario_id' => $event->scenarioId()->toString(),
            'base_model_id' => $event->baseModelId()->toString(),
            'scenario_analysis_id' => $event->scenarioAnalysisId()->toString(),
            'user_id' => $event->userId()->toString(),
            'name' => $result['name'],
            'description' => $result['description'],
            'calculation_id' => $calculationId,
            'is_base_model' => 0,
            'is_scenario' => 1,
            'created_at' => date_format($event->createdAt(), DATE_ATOM),
        ));
    }

    public function onScenarioWasDeleted(ScenarioWasDeleted $event): void
    {

        $this->connection->delete(Table::SCENARIO_LIST,
            [
                'scenario_id' => $event->scenarioId()->toString(),
                'scenario_analysis_id' => $event->scenarioAnalysisId()->toString(),
                'user_id' => $event->userId()->toString()
            ]
        );
    }

    public function onCalculationIdWasChanged(CalculationIdWasChanged $event): void
    {
        $this->connection->update(Table::SCENARIO_LIST, array(
            'calculation_id' => $event->calculationId()->toString()
        ),
            array('scenario_id' => $event->modelId()->toString())
        );
    }
}
