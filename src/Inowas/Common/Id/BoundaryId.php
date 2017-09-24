<?php

declare(strict_types=1);

namespace Inowas\Common\Id;

class BoundaryId
{
    /** @var string */
    private $id;

    public static function fromString(string $id): BoundaryId
    {
        return new self($id);
    }

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function sameValueAs(BoundaryId $other): bool
    {
        return $this->toString() === $other->toString();
    }
}
