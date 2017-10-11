<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

abstract class AbstractPackage implements PackageInterface
{
    const TYPE = 'abs';
    const DESCRIPTION = 'Abstract Package';

    public static function type(): string
    {
        return static::TYPE;
    }

    public static function description(): string
    {
        return static::DESCRIPTION;
    }
}
