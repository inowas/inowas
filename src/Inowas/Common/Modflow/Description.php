<?php

declare(strict_types=1);

namespace Inowas\Common\Modflow;

class Description
{
    /** @var string */
    private $description;

    public static function fromString(string $description): Description
    {
        return new self($description);
    }

    private function __construct(string $description)
    {
        $this->description = $description;
    }

    public function toString(): string
    {
        return $this->description;
    }

    public function sameAs(Description $description): bool
    {
        return $this->toString() === $description->toString();
    }
}
