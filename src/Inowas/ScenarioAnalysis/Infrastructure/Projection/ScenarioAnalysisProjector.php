<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Infrastructure\Projection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Inowas\AppBundle\Model\User;
use Inowas\Common\Id\UserId;
use Inowas\Common\Projection\AbstractDoctrineConnectionProjector;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisDescriptionWasChanged;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisNameWasChanged;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisVisibilityWasChanged;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasCloned;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasCreated;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasDeleted;

class ScenarioAnalysisProjector extends AbstractDoctrineConnectionProjector
{

    /** @var  EntityManager */
    private $entityManager;

    public function __construct(Connection $connection, EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct($connection);

        $schema = new Schema();
        $table = $schema->createTable(Table::SCENARIO_ANALYSIS_LIST);
        $table->addColumn('scenario_analysis_id', 'string', ['length' => 36]);
        $table->addColumn('base_model_id', 'string', ['length' => 36]);
        $table->addColumn('user_id', 'string', ['length' => 36]);
        $table->addColumn('user_name', 'string', ['length' => 255]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('description', 'string', ['length' => 255]);
        $table->addColumn('created_at', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('public', 'smallint', ['default' => 1]);
        $table->setPrimaryKey(['scenario_analysis_id']);
        $table->addIndex(array('base_model_id'));
        $this->addSchema($schema);
    }

    public function onScenarioAnalysisWasCreated(ScenarioAnalysisWasCreated $event): void
    {
        $this->connection->insert(Table::SCENARIO_ANALYSIS_LIST, [
            'scenario_analysis_id' => $event->scenarioAnalysisId()->toString(),
            'user_id' => $event->userId()->toString(),
            'user_name' => $this->getUserNameByUserId($event->userId()),
            'name' => $event->name()->toString(),
            'description' => $event->description()->toString(),
            'base_model_id' => $event->baseModelId()->toString(),
            'created_at' => date_format($event->createdAt(), DATE_ATOM)
        ]);
    }

    public function onScenarioAnalysisWasCloned(ScenarioAnalysisWasCloned $event): void
    {
        $this->connection->insert(Table::SCENARIO_ANALYSIS_LIST, [
            'scenario_analysis_id' => $event->scenarioAnalysisId()->toString(),
            'user_id' => $event->userId()->toString(),
            'user_name' => $this->getUserNameByUserId($event->userId()),
            'name' => $event->name()->toString(),
            'description' => $event->description()->toString(),
            'base_model_id' => $event->baseModelId()->toString(),
            'created_at' => date_format($event->createdAt(), DATE_ATOM)
        ]);
    }

    /**
     * @param ScenarioAnalysisWasDeleted $event
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function onScenarioAnalysisWasDeleted(ScenarioAnalysisWasDeleted $event): void
    {
        $this->connection->delete(
            Table::SCENARIO_ANALYSIS_LIST,
            [
                'scenario_analysis_id' => $event->scenarioAnalysisId()->toString(),
                'user_id' => $event->userId()->toString()
            ]
        );
    }

    public function onScenarioAnalysisNameWasChanged(ScenarioAnalysisNameWasChanged $event): void
    {
        $this->connection->update(Table::SCENARIO_ANALYSIS_LIST,
            ['name' => $event->name()->toString()],
            ['scenario_analysis_id' => $event->scenarioAnalysisId()->toString()]
        );
    }

    public function onScenarioAnalysisDescriptionWasChanged(ScenarioAnalysisDescriptionWasChanged $event): void
    {
        $this->connection->update(Table::SCENARIO_ANALYSIS_LIST,
            ['description' => $event->description()->toString()],
            ['scenario_analysis_id' => $event->scenarioAnalysisId()->toString()]
        );
    }

    public function onScenarioAnalysisVisibilityWasChanged(ScenarioAnalysisVisibilityWasChanged $event): void
    {
        $this->connection->update(Table::SCENARIO_ANALYSIS_LIST,
            ['public' => $event->visibility()->isPublic() ? 1 : 0],
            ['scenario_analysis_id' => $event->scenarioAnalysisId()->toString()]
        );
    }

    private function getUserNameByUserId(UserId $id): string
    {
        $username = '';
        $user = $this->entityManager->getRepository('InowasAppBundle:User')->findOneBy(array('id' => $id->toString()));
        if ($user instanceof User) {
            $username = $user->getName();
        }

        return $username;
    }
}
