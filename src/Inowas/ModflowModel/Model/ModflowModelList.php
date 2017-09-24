<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model;

use Inowas\Common\Id\ModflowId;

interface ModflowModelList
{
    public function get(ModflowId $modelId);

    public function save(ModflowModelAggregate $model);
}
