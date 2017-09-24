<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowModel\Model\AMQP\ModflowCalculationRequest;
use Inowas\ModflowModel\Model\Command\RequestModflowModelCalculation;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\Exception\WriteAccessFailedException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;
use Inowas\ModflowModel\Service\AMQPModflowCalculation;

final class RequestModflowModelCalculationHandler
{

    /** @var  AMQPModflowCalculation */
    private $asyncModflowCalculator;

    /** @var  ModflowModelList */
    private $modelList;

    /**
     * ChangeModflowModelBoundingBoxHandler constructor.
     * @param ModflowModelList $modelList
     * @param AMQPModflowCalculation $asyncModflowCalculator
     */
    public function __construct(
        ModflowModelList $modelList,
        AMQPModflowCalculation $asyncModflowCalculator
    )
    {
        $this->asyncModflowCalculator = $asyncModflowCalculator;
        $this->modelList = $modelList;
    }

    public function __invoke(RequestModflowModelCalculation $command)
    {
        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->modelId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->modelId());
        }

        if (! $modflowModel->userId()->sameValueAs($command->userId())){
            throw WriteAccessFailedException::withUserAndOwner($command->userId(), $modflowModel->userId());
        }

        $request = ModflowCalculationRequest::fromParams($command->userId(), $command->modelId());
        $this->asyncModflowCalculator->calculate($request);

        $modflowModel->calculationRequestWasSent($command->userId());
        $this->modelList->save($modflowModel);
    }
}
