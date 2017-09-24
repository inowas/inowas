<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model;

use Inowas\Common\Soilmodel\BoreLogId;

interface BoreLogList
{
    public function get(BoreLogId $boreLogId);

    public function save(BoreLogAggregate $boreLog);
}
