<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Handler;

use Inowas\ModflowModel\Infrastructure\Projection\ModelList\ModelFinder;
use Inowas\ModflowModel\Model\Command\UpdateAreaGeometry;
use Inowas\ModflowModel\Model\Exception\ModflowModelNotFoundException;
use Inowas\ModflowModel\Model\Exception\WriteAccessFailedException;
use Inowas\ModflowModel\Model\ModflowModelList;
use Inowas\ModflowModel\Model\ModflowModelAggregate;

final class UpdateAreaGeometryHandler
{

    /** @var  ModflowModelList */
    private $modelList;

    /** @var ModelFinder */
    private $modelFinder;

    /**
     * UpdateAreaGeometryHandler constructor.
     * @param ModflowModelList $modelList
     * @param ModelFinder $modelFinder
     */
    public function __construct(ModflowModelList $modelList, ModelFinder $modelFinder)
    {
        $this->modelList = $modelList;
        $this->modelFinder = $modelFinder;
    }

    public function __invoke(UpdateAreaGeometry $command)
    {
        /** @var ModflowModelAggregate $modflowModel */
        $modflowModel = $this->modelList->get($command->modelId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->modelId());
        }

        if (! $modflowModel->userId()->sameValueAs($command->userId())){
            throw WriteAccessFailedException::withUserAndOwner($command->userId(), $modflowModel->userId());
        }

        $polygon = $this->modelFinder->getAreaPolygonByModflowModelId($command->modelId());

        if (! $polygon->sameAs($command->geometry())){
            $modflowModel->updateAreaGeometry($command->userId(), $command->geometry());
            $this->modelList->save($modflowModel);
        }
    }
}
