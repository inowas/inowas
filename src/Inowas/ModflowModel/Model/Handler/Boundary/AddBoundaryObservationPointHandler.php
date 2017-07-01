<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler\Boundary;

use Inowas\Common\Boundaries\ObservationPoint;
use Inowas\ModflowModel\Model\Command\Boundary\AddBoundaryObservationPoint;
use Inowas\ModflowModel\Model\Exception\ModflowBoundaryNotFoundException;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\Exception\WriteAccessFailedException;
use Inowas\ModflowModel\Model\ModflowBoundaryAggregate;
use Inowas\ModflowModel\Model\ModflowBoundaryList;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;
use Inowas\ModflowModel\Service\BoundaryManager;

final class AddBoundaryObservationPointHandler
{

    /** @var  ModflowModelList */
    private $modelList;

    /** @var  ModflowBoundaryList */
    private $boundaryList;

    /** @var  BoundaryManager */
    private $boundaryManager;


    /**
     * AddBoundaryObservationPointHandler constructor.
     * @param ModflowModelList $modelList
     * @param ModflowBoundaryList $boundaryList
     */
    public function __construct(ModflowModelList $modelList, ModflowBoundaryList $boundaryList)
    {
        $this->boundaryList = $boundaryList;
        $this->modelList = $modelList;
    }

    public function __invoke(AddBoundaryObservationPoint $command)
    {
        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->modelId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->modelId());
        }

        if (! $modflowModel->userId()->sameValueAs($command->userId())){
            throw WriteAccessFailedException::withUserAndOwner($command->userId(), $modflowModel->userId());
        }

        /** @var ModflowBoundaryAggregate $boundary */
        $boundary = $this->boundaryList->get($command->boundaryId());

        if (! $boundary){
            throw ModflowBoundaryNotFoundException::withId($command->boundaryId());
        }

        $boundaryType = $this->boundaryManager->getBoundaryTypeByBoundaryId($command->boundaryId());

        $observationPoint = ObservationPoint::fromIdTypeNameAndGeometry(
            $command->observationPointId(),
            $boundaryType,
            $command->observationPointName(),
            $command->geometry()
        );

        $boundary->addObservationPoint($command->userId(), $observationPoint);
    }
}
