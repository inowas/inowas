<?php

namespace Inowas\ModflowModel\Model\Exception;


final class ModflowPackagesNotFoundException extends \InvalidArgumentException
{
    public static function withHash(string $hash): ModflowPackagesNotFoundException
    {
        return new self(sprintf('ModflowCalculation-Packages with hash %s cannot be found.', $hash));
    }
}
