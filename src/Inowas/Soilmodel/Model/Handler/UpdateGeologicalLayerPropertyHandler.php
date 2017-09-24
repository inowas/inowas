<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model\Handler;

use Inowas\Soilmodel\Model\Command\UpdateGeologicalLayerProperty;
use Inowas\Soilmodel\Model\Exception\SoilmodelNotFoundException;
use Inowas\Soilmodel\Model\Exception\WriteAccessFailedException;
use Inowas\Soilmodel\Model\SoilmodelAggregate;
use Inowas\Soilmodel\Model\SoilmodelList;

final class UpdateGeologicalLayerPropertyHandler
{

    /** @var  SoilmodelList */
    private $soilmodelList;


    public function __construct(SoilmodelList $soilmodelList)
    {
        $this->soilmodelList = $soilmodelList;
    }

    public function __invoke(UpdateGeologicalLayerProperty $command)
    {
        $soilmodel = $this->soilmodelList->get($command->soilmodelId());

        if (! $soilmodel instanceof SoilmodelAggregate){
            throw SoilmodelNotFoundException::withSoilModelId($command->soilmodelId());
        }

        if (! $soilmodel->userHasWriteAccess($command->userId())){
            throw WriteAccessFailedException::withSoilModelAndUserId($command->soilmodelId(), $command->userId());
        }

        $soilmodel->updateGeologicalLayerProperty($command->userId(), $command->layerId(), $command->property());
        $this->soilmodelList->save($soilmodel);
    }
}
