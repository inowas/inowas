<?php

declare(strict_types=1);

namespace Inowas\Tool\Model;

interface ToolInstanceList
{
    public function get(ToolId $id);

    public function save(ToolInstanceAggregate $model);
}
