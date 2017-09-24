<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowModel\Model\Command\CreateModflowModel;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;

final class CreateModflowModelHandler
{

    /** @var  ModflowModelList */
    private $modelList;

    public function __construct(ModflowModelList $modelList)
    {
        $this->modelList = $modelList;
    }

    public function __invoke(CreateModflowModel $command)
    {
        $modflowModel = ModflowModelAggregate::create(
            $command->modelId(),
            $command->userId(),
            $command->geometry(),
            $command->gridSize(),
            $command->boundingBox()
        );

        $modflowModel->changeName($command->userId(), $command->name());
        $modflowModel->changeDescription($command->userId(), $command->description());
        $modflowModel->updateTimeUnit($command->userId(), $command->timeUnit());
        $modflowModel->updateLengthUnit($command->userId(), $command->lengthUnit());
        $modflowModel->changeVisibility($command->userId(), $command->visibility());

        $this->modelList->save($modflowModel);
    }
}
