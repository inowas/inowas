<?php

namespace Inowas\ModflowBundle\DataFixtures\Scenarios\RioPrimero;
use Inowas\Common\Geometry\Geometry;
use Inowas\Common\Geometry\Polygon;
use Inowas\Common\Grid\GridSize;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\LengthUnit;
use Inowas\Common\Modflow\Description;
use Inowas\Common\Modflow\Name;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\ParameterName;
use Inowas\Common\Modflow\TimeUnit;
use Inowas\Common\Status\Visibility;
use Inowas\ModflowModel\Model\Command\ChangeFlowPackage;
use Inowas\ModflowModel\Model\Command\CreateModflowModel;
use Inowas\ModflowBundle\DataFixtures\Scenarios\LoadScenarioBase;
use Inowas\ModflowModel\Model\Command\UpdateModflowPackageParameter;
use Inowas\ModflowModel\Model\Packages\OcStressPeriod;
use Inowas\ModflowModel\Model\Packages\OcStressPeriodData;

class RioPrimeroArea extends LoadScenarioBase
{

    public function load(): void
    {
        $this->loadUsers($this->container->get('fos_user.user_manager'));

        $commandBus = $this->container->get('prooph_service_bus.modflow_command_bus');
        $ownerId = UserId::fromString($this->ownerId);

        $baseModelId = ModflowId::generate();
        $polygon = new Polygon([[
            [-63.687336, -31.313615],
            [-63.687336, -31.367449],
            [-63.569260, -31.367449],
            [-63.569260, -31.313615],
            [-63.687336, -31.313615]
        ]], 4326);
        $boundingBox = $this->container->get('inowas.geotools.geotools_service')->getBoundingBox(Geometry::fromPolygon($polygon));
        $gridSize = GridSize::fromXY(75, 40);
        $commandBus->dispatch(CreateModflowModel::newWithAllParams(
            $ownerId,
            $baseModelId,
            Name::fromString('Rio Primero Model Area'),
            Description::fromString('Rio Primero Model Area'),
            $polygon,
            $gridSize,
            $boundingBox,
            TimeUnit::fromInt(TimeUnit::DAYS),
            LengthUnit::fromInt(LengthUnit::METERS),
            Visibility::public()
        ));

        $commandBus->dispatch(ChangeFlowPackage::forModflowModel($ownerId, $baseModelId, PackageName::fromString('upw')));
        $ocStressPeriodData = OcStressPeriodData::create()->addStressPeriod(OcStressPeriod::fromParams(0, 0, ['save head', 'save drawdown']));
        $commandBus->dispatch(UpdateModflowPackageParameter::byUserModelIdAndPackageData($ownerId, $baseModelId, PackageName::fromString('oc'), ParameterName::fromString('ocStressPeriodData'), $ocStressPeriodData));
    }
}
