<?php

declare(strict_types=1);

namespace Tests\Inowas\ModflowBundle\Functional;

use Inowas\Common\Boundaries\Metadata;
use Inowas\Common\Boundaries\ObservationPoint;
use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Geometry\Point;
use Inowas\Common\Grid\ActiveCells;
use Inowas\Common\Grid\AffectedLayers;
use Inowas\Common\Grid\BoundingBox;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Modflow\Laytyp;
use Inowas\Common\Modflow\Laywet;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\ModflowModel;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\ParameterName;
use Inowas\Common\Modflow\StressPeriod;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Common\Modflow\Version;
use Inowas\Common\Status\Visibility;
use Inowas\ModflowModel\Model\Command\AddBoundary;
use Inowas\ModflowModel\Model\Command\AddLayer;
use Inowas\ModflowModel\Model\Command\ChangeBoundingBox;
use Inowas\ModflowModel\Model\Command\ChangeFlowPackage;
use Inowas\ModflowModel\Model\Command\ChangeGridSize;
use Inowas\ModflowModel\Model\Command\CloneModflowModel;
use Inowas\ModflowModel\Model\Command\CreateModflowModel;
use Inowas\ModflowModel\Model\Command\RemoveBoundary;
use Inowas\ModflowModel\Model\Command\UpdateBoundary;
use Inowas\ModflowModel\Model\Command\UpdateModflowModel;
use Inowas\ModflowModel\Model\Command\UpdateModflowPackageParameter;
use Inowas\ModflowModel\Model\Command\UpdateStressPeriods;
use Inowas\ModflowModel\Model\Event\NameWasChanged;
use Inowas\ModflowModel\Model\ModflowModelAggregate;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Boundaries\WellDateTimeValue;
use Inowas\Common\Id\UserId;
use Inowas\Common\Boundaries\WellBoundary;
use Inowas\Common\Boundaries\WellType;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisDescription;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisName;
use Inowas\Tool\Model\Command\CreateToolInstance;
use Inowas\Tool\Model\Command\DeleteToolInstance;
use Inowas\Tool\Model\Command\UpdateToolInstance;
use Inowas\Tool\Model\ToolData;
use Inowas\Tool\Model\ToolId;
use Inowas\Tool\Model\ToolType;
use Prooph\ServiceBus\Exception\CommandDispatchException;
use Tests\Inowas\ModflowBundle\EventSourcingBaseTest;

class ToolInstanceEventSourcingTest extends EventSourcingBaseTest
{
    /**
     * @test
     */
    public function it_can_create_a_tool_instance(): void
    {
        $toolId = ToolId::generate();
        $ownerId = UserId::generate();
        $toolType = ToolType::fromString('T01');
        $name = Name::fromString('ToolName');
        $description = Description::fromString('ToolDescription');
        $data = ToolData::fromArray([1,3,5, 'test' => '1, 3, 5']);

        $this->commandBus->dispatch(CreateToolInstance::newWithAllParams($ownerId, $toolId, $toolType, $name, $description, $data));

        $tool = $this->container->get('inowas.tool.tools_finder')->findById($toolId);
        $this->assertNotNull($tool);

        $this->assertArrayHasKey('id', $tool);
        $this->assertEquals($toolId->toString(), $tool['id']);

        $this->assertArrayHasKey('name', $tool);
        $this->assertEquals($name->toString(), $tool['name']);

        $this->assertArrayHasKey('description', $tool);
        $this->assertEquals($description->toString(), $tool['description']);

        $this->assertArrayHasKey('project', $tool);
        $this->assertArrayHasKey('application', $tool);

        $this->assertArrayHasKey('tool', $tool);
        $this->assertEquals($toolType->toString(), $tool['tool']);

        $this->assertArrayHasKey('created_at', $tool);

        $this->assertArrayHasKey('user_id', $tool);
        $this->assertEquals($ownerId->toString(), $tool['user_id']);

        $this->assertArrayHasKey('user_name', $tool);

        $this->assertArrayHasKey('public', $tool);
        $this->assertEquals(1, $tool['public']);

        $this->assertArrayHasKey('data', $tool);
        $this->assertEquals($data->toArray(), $tool['data']);
    }

    /**
     * @test
     */
    public function it_can_update_a_tool_instance(): void
    {
        $toolId = ToolId::generate();
        $ownerId = UserId::generate();
        $toolType = ToolType::fromString('T01');
        $name = Name::fromString('ToolName');
        $description = Description::fromString('ToolDescription');
        $data = ToolData::fromArray([1,3,5, 'test' => '1, 3, 5']);
        $this->commandBus->dispatch(CreateToolInstance::newWithAllParams($ownerId, $toolId, $toolType, $name, $description, $data));

        $name = Name::fromString('UpdatedToolName');
        $description = Description::fromString('UpdatedToolDescription');
        $data = ToolData::fromArray([1,3,5, 'updatedTest' => '1, 3, 5']);
        $this->commandBus->dispatch(UpdateToolInstance::newWithAllParams($ownerId, $toolId, $name, $description, $data));

        $tool = $this->container->get('inowas.tool.tools_finder')->findById($toolId);
        $this->assertNotNull($tool);

        $this->assertArrayHasKey('id', $tool);
        $this->assertEquals($toolId->toString(), $tool['id']);

        $this->assertArrayHasKey('name', $tool);
        $this->assertEquals($name->toString(), $tool['name']);

        $this->assertArrayHasKey('description', $tool);
        $this->assertEquals($description->toString(), $tool['description']);

        $this->assertArrayHasKey('project', $tool);
        $this->assertArrayHasKey('application', $tool);

        $this->assertArrayHasKey('tool', $tool);
        $this->assertEquals($toolType->toString(), $tool['tool']);

        $this->assertArrayHasKey('created_at', $tool);

        $this->assertArrayHasKey('user_id', $tool);
        $this->assertEquals($ownerId->toString(), $tool['user_id']);

        $this->assertArrayHasKey('user_name', $tool);

        $this->assertArrayHasKey('public', $tool);
        $this->assertEquals(1, $tool['public']);

        $this->assertArrayHasKey('data', $tool);
        $this->assertEquals($data->toArray(), $tool['data']);
    }

    /**
     * @test
     */
    public function it_can_delete_a_tool_instance(): void
    {
        $toolId = ToolId::generate();
        $ownerId = UserId::generate();
        $toolType = ToolType::fromString('T01');
        $name = Name::fromString('ToolName');
        $description = Description::fromString('ToolDescription');
        $data = ToolData::fromArray([1,3,5, 'test' => '1, 3, 5']);
        $this->commandBus->dispatch(CreateToolInstance::newWithAllParams($ownerId, $toolId, $toolType, $name, $description, $data));

        $tool = $this->container->get('inowas.tool.tools_finder')->findById($toolId);
        $this->assertNotNull($tool);

        $this->commandBus->dispatch(DeleteToolInstance::newWithAllParams($ownerId, $toolId));

        $tool = $this->container->get('inowas.tool.tools_finder')->findById($toolId);
        $this->assertNull($tool);
    }
}
