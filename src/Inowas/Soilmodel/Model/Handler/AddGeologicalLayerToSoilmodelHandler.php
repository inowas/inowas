<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model\Handler;

use Inowas\Soilmodel\Model\Exception\SoilmodelNotFoundException;
use Inowas\Soilmodel\Model\Exception\WriteAccessFailedException;
use Inowas\Soilmodel\Model\Command\AddGeologicalLayerToSoilmodel;
use Inowas\Common\Soilmodel\GeologicalLayerValues;
use Inowas\Soilmodel\Model\SoilmodelAggregate;
use Inowas\Soilmodel\Model\SoilmodelList;

final class AddGeologicalLayerToSoilmodelHandler
{

    /** @var  SoilmodelList */
    private $soilmodelList;


    public function __construct(SoilmodelList $soilmodelList)
    {
        $this->soilmodelList = $soilmodelList;
    }

    public function __invoke(AddGeologicalLayerToSoilmodel $command)
    {
        $soilmodel = $this->soilmodelList->get($command->soilmodelId());

        if (! $soilmodel instanceof SoilmodelAggregate){
            throw SoilmodelNotFoundException::withSoilModelId($command->soilmodelId());
        }

        if (! $soilmodel->userHasWriteAccess($command->userId())){
            throw WriteAccessFailedException::withSoilModelAndUserId($command->soilmodelId(), $command->userId());
        }

        $soilmodel->addGeologicalLayer($command->userId(), $command->layer());
        $soilmodel->updateGeologicalLayerValues($command->layer()->id(), $command->layer()->layerNumber(), GeologicalLayerValues::fromDefault());
        $this->soilmodelList->save($soilmodel);
    }
}
