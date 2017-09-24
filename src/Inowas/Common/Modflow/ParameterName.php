<?php

declare(strict_types=1);

namespace Inowas\Common\Modflow;

class ParameterName
{

    /** @var string */
    protected $name;

    public static function fromString(string $name): ParameterName
    {
        return new self($name);
    }

    private function __construct(string $name)
    {
        $this->name = $name;
    }

    public function toString(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
