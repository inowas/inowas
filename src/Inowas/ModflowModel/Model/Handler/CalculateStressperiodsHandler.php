<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowModel\Model\Command\CalculateStressPeriods;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;
use Inowas\ModflowModel\Service\ModflowModelManager;

final class CalculateStressperiodsHandler
{
    /** @var  ModflowModelList */
    private $modelList;

    /** @var  ModflowModelManager */
    private $modelManager;

    /**
     * @param ModflowModelList $modelList
     * @param ModflowModelManager $modelManager
     */
    public function __construct(ModflowModelList $modelList, ModflowModelManager $modelManager) {
        $this->modelList = $modelList;
        $this->modelManager = $modelManager;
    }

    public function __invoke(CalculateStressPeriods $command)
    {
        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->modflowId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->modflowId());
        }

        $timeUnit = $this->modelManager->getTimeUnitByModelId($command->modflowId());
        $stressperiods = $this->modelManager->calculateStressPeriods($command->modflowId(), $command->start(), $command->end(), $timeUnit);

        #if ($command->initialStressPeriodSteady()) {
        #    // FixMe !! Implement This
        #    $stressperiods->setFirstStressPeriodSteady($command->initialStressPeriodSteady());
        #}

        #$stressperiods->setNstpEqualPerlenForTransient();
        $modflowModel->updateStressPeriods($command->userId(), $stressperiods);

        $this->modelList->save($modflowModel);
    }
}
