<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model\Handler;

use Inowas\Soilmodel\Model\Command\RemoveHorizonFromBoreLog;
use Inowas\Soilmodel\Model\Exception\BoreLogNotFoundException;
use Inowas\Soilmodel\Model\BoreLogAggregate;
use Inowas\Soilmodel\Model\BoreLogList;

final class RemoveHorizonFromBoreLogHandler
{

    /** @var  BoreLogList */
    private $boreLogList;


    public function __construct(BoreLogList $boreLogList)
    {
        $this->boreLogList = $boreLogList;
    }

    public function __invoke(RemoveHorizonFromBoreLog $command)
    {
        $boreLog = $this->boreLogList->get($command->boreLogId());

        if (! $boreLog instanceof BoreLogAggregate){
            throw BoreLogNotFoundException::withBoreLogId($command->boreLogId());
        }

        $boreLog->removeHorizon($command->userId(), $command->horizonId());
        $this->boreLogList->save($boreLog);
    }
}
