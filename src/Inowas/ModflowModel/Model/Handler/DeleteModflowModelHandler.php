<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowBundle\Exception\AccessDeniedException;
use Inowas\ModflowModel\Model\Command\DeleteModflowModel;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;

final class DeleteModflowModelHandler
{

    /** @var  ModflowModelList */
    private $modelList;

    public function __construct(ModflowModelList $modelList)
    {
        $this->modelList = $modelList;
    }

    public function __invoke(DeleteModflowModel $command)
    {
        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->modelId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->modelId());
        }

        if (! $command->userId()->sameValueAs($modflowModel->userId())){
            throw AccessDeniedException::withMessage(sprintf(
                'Access denied to delete Model with id %s.',
                $command->modelId()->toString()
            ));
        }

        $modflowModel->delete($command->userId());
        $this->modelList->save($modflowModel);
    }
}
