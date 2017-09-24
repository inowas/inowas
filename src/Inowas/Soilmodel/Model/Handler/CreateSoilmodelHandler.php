<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model\Handler;

use Inowas\Soilmodel\Model\Command\CreateSoilmodel;
use Inowas\Soilmodel\Model\SoilmodelAggregate;
use Inowas\Soilmodel\Model\SoilmodelList;

final class CreateSoilmodelHandler
{
    /** @var  SoilmodelList */
    private $soilmodelList;

    public function __construct(SoilmodelList $soilmodelList)
    {
        $this->soilmodelList = $soilmodelList;
    }

    public function __invoke(CreateSoilmodel $command)
    {
        $soilmodel = SoilmodelAggregate::create(
            $command->userId(),
            $command->soilmodelId()
        );

        $this->soilmodelList->save($soilmodel);
    }
}
