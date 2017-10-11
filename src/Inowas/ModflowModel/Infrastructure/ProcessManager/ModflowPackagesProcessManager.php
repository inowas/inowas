<?php

namespace Inowas\ModflowModel\Infrastructure\ProcessManager;

use Inowas\ModflowModel\Model\Command\UpdateCalculationId;
use Inowas\ModflowModel\Model\Event\FlowPackageWasChanged;
use Inowas\ModflowModel\Model\Event\LengthUnitWasUpdated;
use Inowas\ModflowModel\Model\Event\ModflowModelWasCloned;
use Inowas\ModflowModel\Model\Event\ModflowModelWasCreated;
use Inowas\ModflowModel\Model\Event\ModflowPackageParameterWasUpdated;
use Inowas\ModflowModel\Model\Event\ModflowPackageWasUpdated;
use Inowas\ModflowModel\Model\Event\StressPeriodsWereUpdated;
use Inowas\ModflowModel\Model\Event\TimeUnitWasUpdated;
use Inowas\ModflowModel\Service\ModflowPackagesManager;
use Prooph\Common\Messaging\DomainEvent;
use Prooph\ServiceBus\CommandBus;

class ModflowPackagesProcessManager
{
    /** @var  CommandBus */
    private $commandBus;

    /** @var  ModflowPackagesManager */
    private $packagesManager;

    public function __construct(CommandBus $commandBus, ModflowPackagesManager $packagesManager)
    {
        $this->commandBus = $commandBus;
        $this->packagesManager = $packagesManager;
    }

    public function onFlowPackageWasChanged(FlowPackageWasChanged $event): void
    {
        $packages = $this->packagesManager->getPackagesByModelId($event->modelId());
        $packages->changeFlowPackage($event->packageName());
        $calculationId = $this->packagesManager->savePackages($packages);
        $this->commandBus->dispatch(UpdateCalculationId::withId($event->modelId(), $calculationId));
    }

    public function onLengthUnitWasUpdated(LengthUnitWasUpdated $event): void
    {
        $packages = $this->packagesManager->getPackagesByModelId($event->modelId());
        $packages->updateLengthUnit($event->lengthUnit());
        $calculationId = $this->packagesManager->savePackages($packages);
        $this->commandBus->dispatch(UpdateCalculationId::withId($event->modelId(), $calculationId));
    }

    public function onModflowModelWasCloned(ModflowModelWasCloned $event): void
    {
        $calculationId = $this->packagesManager->getCalculationId($event->baseModelId());
        $this->commandBus->dispatch(UpdateCalculationId::withId($event->modelId(), $calculationId));
    }

    public function onModflowModelWasCreated(ModflowModelWasCreated $event): void
    {
        $calculationId = $this->packagesManager->createFromDefaultsAndSave();
        $this->commandBus->dispatch(UpdateCalculationId::withId($event->modelId(), $calculationId));
    }

    public function onStressPeriodsWereUpdated(StressPeriodsWereUpdated $event): void
    {
        $calculationId = $this->packagesManager->recalculateStressperiods($event->modelId(), $event->stressPeriods());
        $this->commandBus->dispatch(UpdateCalculationId::withId($event->modelId(), $calculationId));
    }

    public function onTimeUnitWasUpdated(TimeUnitWasUpdated $event): void
    {
        $packages = $this->packagesManager->getPackagesByModelId($event->modelId());
        $packages->updateTimeUnit($event->timeUnit());
        $calculationId = $this->packagesManager->savePackages($packages);
        $this->commandBus->dispatch(UpdateCalculationId::withId($event->modelId(), $calculationId));
    }

    public function onModflowPackageParameterWasUpdated(ModflowPackageParameterWasUpdated $event): void
    {
        $packages = $this->packagesManager->getPackagesByModelId($event->modelId());
        $packages->updatePackageParameter($event->packageName()->toString(), $event->parameterName()->toString(), $event->parameterData());
        $calculationId = $this->packagesManager->savePackages($packages);
        $this->commandBus->dispatch(UpdateCalculationId::withId($event->modelId(), $calculationId));
    }

    public function onModflowPackageWasUpdated(ModflowPackageWasUpdated $event): void
    {
        $packages = $this->packagesManager->getPackagesByModelId($event->modelId());
        $packages->mergePackageData($event->packageName(), $event->data());
        $calculationId = $this->packagesManager->savePackages($packages);
        $this->commandBus->dispatch(UpdateCalculationId::withId($event->modelId(), $calculationId));
    }

    public function onEvent(DomainEvent $e): void
    {
        $handler = $this->determineEventMethodFor($e);
        if (! method_exists($this, $handler)) {
            throw new \RuntimeException(sprintf(
                'Missing event method %s for projector %s',
                $handler,
                get_class($this)
            ));
        }
        $this->{$handler}($e);
    }

    protected function determineEventMethodFor(DomainEvent $e): string
    {
        return 'on' . implode(array_slice(explode('\\', get_class($e)), -1));
    }
}
