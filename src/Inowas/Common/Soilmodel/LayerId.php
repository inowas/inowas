<?php

declare(strict_types=1);

namespace Inowas\Common\Soilmodel;

class LayerId
{
    /** @var string */
    private $id;

    public static function fromString(string $id): LayerId
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

    public function sameValueAs(LayerId $other): bool
    {
        return $this->toString() === $other->toString();
    }
}
