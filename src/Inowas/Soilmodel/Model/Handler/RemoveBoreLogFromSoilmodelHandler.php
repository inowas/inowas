<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model\Handler;

use Inowas\Soilmodel\Model\Exception\BoreLogNotFoundException;
use Inowas\Soilmodel\Model\Exception\SoilmodelNotFoundException;
use Inowas\Soilmodel\Model\Exception\WriteAccessFailedException;
use Inowas\Soilmodel\Model\BoreLogAggregate;
use Inowas\Soilmodel\Model\BoreLogList;
use Inowas\Soilmodel\Model\Command\RemoveBoreLogFromSoilmodel;
use Inowas\Soilmodel\Model\SoilmodelAggregate;
use Inowas\Soilmodel\Model\SoilmodelList;

final class RemoveBoreLogFromSoilmodelHandler
{

    /** @var  BoreLogList */
    private $boreLogList;

    /** @var  SoilmodelList */
    private $soilmodelList;


    public function __construct(SoilmodelList $soilmodelList, BoreLogList $boreLogList)
    {
        $this->boreLogList = $boreLogList;
        $this->soilmodelList = $soilmodelList;
    }

    public function __invoke(RemoveBoreLogFromSoilmodel $command)
    {
        $soilmodel = $this->soilmodelList->get($command->soilmodelId());

        if (! $soilmodel instanceof SoilmodelAggregate){
            throw SoilmodelNotFoundException::withSoilModelId($command->soilmodelId());
        }

        $boreLog = $this->boreLogList->get($command->boreLogId());

        if (! $boreLog instanceof BoreLogAggregate){
            throw BoreLogNotFoundException::withBoreLogId($command->boreLogId());
        }

        if (! $soilmodel->userHasWriteAccess($command->userId())){
            throw WriteAccessFailedException::withSoilModelAndUserId($command->soilmodelId(), $command->userId());
        }

        $soilmodel->removeBoreLog($command->userId(), $command->boreLogId());
        $this->boreLogList->save($boreLog);
    }
}
