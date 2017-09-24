<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model\Handler;

use Inowas\Soilmodel\Model\Command\CloneSoilmodel;
use Inowas\Soilmodel\Model\Exception\SoilmodelNotFoundException;
use Inowas\Soilmodel\Model\SoilmodelAggregate;
use Inowas\Soilmodel\Model\SoilmodelList;

final class CloneSoilmodelHandler
{
    /** @var  SoilmodelList */
    private $soilmodelList;

    public function __construct(SoilmodelList $soilmodelList)
    {
        $this->soilmodelList = $soilmodelList;
    }

    public function __invoke(CloneSoilmodel $command)
    {

        /** @var SoilmodelAggregate $soilmodel */
        $soilmodel = $this->soilmodelList->get($command->fromSoilmodelId());

        if (! $soilmodel instanceof SoilmodelAggregate){
            throw SoilmodelNotFoundException::withSoilModelId($command->fromSoilmodelId());
        }

        $soilmodel = SoilmodelAggregate::clone(
            $command->userId(),
            $command->newSoilmodelId(),
            $soilmodel
        );

        $this->soilmodelList->save($soilmodel);
    }
}
