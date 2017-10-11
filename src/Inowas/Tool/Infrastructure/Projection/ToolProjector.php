<?php

declare(strict_types=1);

namespace Inowas\Tool\Infrastructure\Projection;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\EntityManager;
use Inowas\AppBundle\Model\User;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\ModflowModel;
use Inowas\Common\Projection\AbstractDoctrineConnectionProjector;
use Inowas\ModflowModel\Model\Event\DescriptionWasChanged;
use Inowas\ModflowModel\Model\Event\ModflowModelWasCloned;
use Inowas\ModflowModel\Model\Event\ModflowModelWasCreated;
use Inowas\ModflowModel\Model\Event\ModflowModelWasDeleted;
use Inowas\ModflowModel\Model\Event\NameWasChanged;
use Inowas\ModflowModel\Model\Event\VisibilityWasChanged;
use Inowas\ModflowModel\Service\ModflowModelManager;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisDescriptionWasChanged;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisNameWasChanged;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisVisibilityWasChanged;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasCloned;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasCreated;
use Inowas\ScenarioAnalysis\Model\Event\ScenarioAnalysisWasDeleted;
use Inowas\Tool\Model\Event\ToolInstanceDataWasUpdated;
use Inowas\Tool\Model\Event\ToolInstanceDescriptionWasUpdated;
use Inowas\Tool\Model\Event\ToolInstanceNameWasUpdated;
use Inowas\Tool\Model\Event\ToolInstanceWasCreated;
use Inowas\Tool\Model\Event\ToolInstanceWasDeleted;

class ToolProjector extends AbstractDoctrineConnectionProjector
{

    /** @var  EntityManager */
    private $entityManager;

    /** @var  ModflowModelManager */
    private $modelManager;

    public function __construct(Connection $connection, EntityManager $entityManager, ModflowModelManager $manager)
    {

        $this->entityManager = $entityManager;
        $this->modelManager = $manager;

        parent::__construct($connection);

        $schema = new Schema();
        $table = $schema->createTable(Table::TOOL_LIST);
        $table->addColumn('id', 'string', ['length' => 36]);
        $table->addColumn('name', 'string', ['length' => 255, 'default' => '']);
        $table->addColumn('description', 'string', ['length' => 255, 'default' => '']);
        $table->addColumn('project', 'string', ['length' => 255, 'default' => '']);
        $table->addColumn('application', 'string', ['length' => 255, 'default' => '']);
        $table->addColumn('tool', 'string', ['length' => 255, 'default' => '']);
        $table->addColumn('created_at', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('user_id', 'string', ['length' => 36]);
        $table->addColumn('user_name', 'string', ['length' => 255]);
        $table->addColumn('public', 'smallint', ['default' => 1]);
        $table->addColumn('data', 'text', ['default' => '[]']);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['tool']);
        $table->addIndex(['user_id']);
        $this->addSchema($schema);
    }

    public function onToolInstanceDataWasUpdated(ToolInstanceDataWasUpdated $event): void
    {
        $this->connection->update(Table::TOOL_LIST,
            ['data' => json_encode($event->data()->toArray())],
            ['id' => $event->id()->toString()]
        );
    }

    public function onToolInstanceDescriptionWasUpdated(ToolInstanceDescriptionWasUpdated $event): void
    {
        $this->connection->update(Table::TOOL_LIST,
            ['description' => $event->description()->toString()],
            ['id' => $event->id()->toString()]
        );
    }

    public function onToolInstanceNameWasUpdated(ToolInstanceNameWasUpdated $event): void
    {
        $this->connection->update(Table::TOOL_LIST,
            ['name' => $event->name()->toString()],
            ['id' => $event->id()->toString()]
        );
    }

    public function onToolInstanceWasCreated(ToolInstanceWasCreated $event): void
    {
        $this->connection->insert(Table::TOOL_LIST, array(
            'id' => $event->id()->toString(),
            'application' => '',
            'project' => '',
            'tool' => $event->type()->toString(),
            'user_id' => $event->userId()->toString(),
            'user_name' => $this->getUserNameByUserId($event->userId()),
            'created_at' => date_format($event->createdAt(), DATE_ATOM)
        ));
    }

    public function onToolInstanceWasDeleted(ToolInstanceWasDeleted $event): void
    {
        $this->connection->delete(Table::TOOL_LIST,
            ['id' => $event->id()->toString()]
        );
    }

    public function onModflowModelWasCreated(ModflowModelWasCreated $event): void
    {
        $this->connection->insert(Table::TOOL_LIST, array(
            'id' => $event->modelId()->toString(),
            'application' => '',
            'project' => '',
            'tool' => 'T03',
            'user_id' => $event->userId()->toString(),
            'user_name' => $this->getUserNameByUserId($event->userId()),
            'created_at' => date_format($event->createdAt(), DATE_ATOM)
        ));
    }

    public function onNameWasChanged(NameWasChanged $event): void
    {
        $this->connection->update(Table::TOOL_LIST, array(
            'name' => $event->name()->toString()
        ), array(
            'id' => $event->modelId()->toString()
        ));
    }

    public function onDescriptionWasChanged(DescriptionWasChanged $event): void
    {
        $this->connection->update(Table::TOOL_LIST, array(
            'description' => $event->description()->toString()
        ), array(
            'id' => $event->modelId()->toString()
        ));
    }

    public function onModflowModelWasCloned(ModflowModelWasCloned $event): void
    {
        if (! $event->isTool()) {
            return;
        }

        $model = $this->modelManager->findModel($event->modelId(), $event->userId());

        if (! $model instanceof ModflowModel) {
            return;
        }

        $this->connection->insert(Table::TOOL_LIST, array(
            'id' => $event->modelId()->toString(),
            'name' => $model->name()->toString(),
            'description' => $model->description()->toString(),
            'application' => '',
            'project' => '',
            'tool' => 'T03',
            'user_id' => $event->userId()->toString(),
            'user_name' => $this->getUserNameByUserId($event->userId()),
            'created_at' => date_format($event->createdAt(), DATE_ATOM)
        ));
    }

    public function onModflowModelWasDeleted(ModflowModelWasDeleted $event): void
    {
        $this->connection->delete(Table::TOOL_LIST, array(
            'id' => $event->modelId()->toString()
        ));
    }

    public function onScenarioAnalysisWasCreated(ScenarioAnalysisWasCreated $event): void
    {
        $this->connection->insert(Table::TOOL_LIST, array(
            'id' => $event->scenarioAnalysisId()->toString(),
            'name' => $event->name()->toString(),
            'description' => $event->description()->toString(),
            'application' => '',
            'project' => '',
            'tool' => 'T07',
            'user_id' => $event->userId()->toString(),
            'user_name' => $this->getUserNameByUserId($event->userId()),
            'created_at' => date_format($event->createdAt(), DATE_ATOM)
        ));
    }

    public function onScenarioAnalysisWasCloned(ScenarioAnalysisWasCloned $event): void
    {
        $this->connection->insert(Table::TOOL_LIST, array(
            'id' => $event->scenarioAnalysisId()->toString(),
            'name' => $event->name()->toString(),
            'description' => $event->description()->toString(),
            'application' => '',
            'project' => '',
            'tool' => 'T07',
            'user_id' => $event->userId()->toString(),
            'user_name' => $this->getUserNameByUserId($event->userId()),
            'created_at' => date_format($event->createdAt(), DATE_ATOM)
        ));
    }

    public function onScenarioAnalysisWasDeleted(ScenarioAnalysisWasDeleted $event): void
    {
        $this->connection->delete(
            Table::TOOL_LIST,
            array('id' => $event->scenarioAnalysisId()->toString(), 'user_id' => $event->userId()->toString())
        );
    }

    public function onScenarioAnalysisNameWasChanged(ScenarioAnalysisNameWasChanged $event): void
    {
        $this->connection->update(Table::TOOL_LIST,
            array('name' => $event->name()->toString()),
            array('id' => $event->scenarioAnalysisId()->toString())
        );
    }

    public function onScenarioAnalysisDescriptionWasChanged(ScenarioAnalysisDescriptionWasChanged $event):void
    {
        $this->connection->update(Table::TOOL_LIST,
            array('description' => $event->description()->toString()),
            array('id' => $event->scenarioAnalysisId()->toString())
        );
    }

    public function onVisibilityWasChanged(VisibilityWasChanged $event): void
    {
        $this->connection->update(Table::TOOL_LIST,
            array(
                'public' => $event->visibility()->isPublic() ? 1 : 0
            ),
            array('id' => $event->modelId()->toString())
        );
    }

    public function onScenarioAnalysisVisibilityWasChanged(ScenarioAnalysisVisibilityWasChanged $event): void
    {
        $this->connection->update(Table::TOOL_LIST,
            array(
                'public' => $event->visibility()->isPublic() ? 1 : 0
            ),
            array('id' => $event->scenarioAnalysisId()->toString())
        );
    }

    private function getUserNameByUserId(UserId $id): string
    {
        $username = '';
        $user = $this->entityManager->getRepository('InowasAppBundle:User')->findOneBy(array('id' => $id->toString()));
        if ($user instanceof User){
            $username = $user->getName();
        }

        return $username;
    }
}
