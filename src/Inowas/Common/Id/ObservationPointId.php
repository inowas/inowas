<?php

declare(strict_types=1);

namespace Inowas\Common\Id;

class ObservationPointId
{
    /** @var string */
    private $id;

    public static function fromString(string $id): ObservationPointId
    {
        return new self(strtolower($id));
    }

    private function __construct(string $id)
    {
        $this->id = $id;
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function sameValueAs(ObservationPointId $other): bool
    {
        return $this->toString() === $other->toString();
    }
}
