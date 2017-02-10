<?php

declare(strict_types=1);

namespace Inowas\Modflow\Model\Handler;

use Inowas\Modflow\Model\Command\ChangeModflowModelArea;
use Inowas\Modflow\Model\Exception\ModflowModelNotFoundException;
use Inowas\Modflow\Model\ModflowModelList;
use Inowas\Modflow\ModflowModel;

final class ChangeModflowModelAreaHandler
{

    /** @var  ModflowModelList */
    private $modelList;

    /**
     * @param ModflowModelList $modelList
     */
    private function __construct(ModflowModelList $modelList)
    {
        $this->modelList = $modelList;
    }

    public function __invoke(ChangeModflowModelArea $command)
    {
        /** @var ModflowModel $modflowModel */
        $modflowModel = $this->modelList->get($command->modflowModelId());

        if (!$modflowModel){
            throw ModflowModelNotFoundException::withModelId($command->modflowModelId());
        }

        $modflowModel->changeArea($command->area());
    }
}