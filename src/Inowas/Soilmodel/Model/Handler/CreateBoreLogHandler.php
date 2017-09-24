<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model\Handler;

use Inowas\Soilmodel\Model\Command\CreateBoreLog;
use Inowas\Soilmodel\Model\BoreLogAggregate;
use Inowas\Soilmodel\Model\BoreLogList;

final class CreateBoreLogHandler
{

    /** @var  BoreLogList */
    private $boreLogList;


    public function __construct(BoreLogList $boreLogList)
    {
        $this->boreLogList = $boreLogList;
    }

    public function __invoke(CreateBoreLog $command)
    {

        $boreLog = BoreLogAggregate::create(
            $command->userId(),
            $command->boreLogId(),
            $command->name(),
            $command->location()
        );

        $this->boreLogList->save($boreLog);
    }
}
