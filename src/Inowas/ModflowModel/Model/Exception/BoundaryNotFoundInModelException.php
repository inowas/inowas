<?php

namespace Inowas\ModflowModel\Model\Exception;

use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\ModflowId;

final class BoundaryNotFoundInModelException extends \InvalidArgumentException
{
    public static function withIds(ModflowId $modelId, BoundaryId $boundaryId)
    {
        return new self(sprintf('ModflowModel with id: %s does not contain a boundary with id %s.', $modelId->toString(), $boundaryId->toString()));
    }
}
