<?php

namespace Inowas\Common\Status;


class Visibility
{

    /** @var bool $isPublic */
    private $isPublic;

    public static function public(): Visibility
    {
        return new self(true);
    }

    public static function private(): Visibility
    {
        return new self(false);
    }

    public static function fromBool(bool $isPublic): Visibility
    {
        return new self($isPublic);
    }

    private function __construct(bool $isPublic)
    {
        $this->isPublic = $isPublic;
    }

    public function isPublic(): bool
    {
        return $this->isPublic;
    }

    public function isPrivate(): bool
    {
        return !$this->isPublic;
    }

    public function toBool(): bool
    {
        return $this->isPublic;
    }

    public function sameAs($name): bool
    {
        if (! $name instanceof self) {
            return false;
        }

        return $name->toBool() === $this->toBool();
    }
}
