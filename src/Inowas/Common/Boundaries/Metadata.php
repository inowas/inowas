<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

class Metadata
{
    /**
     * @var array
     */
    private $metadata;

    public static function create(): Metadata
    {
        return new self();
    }

    public static function fromArray(array $arr): Metadata
    {
        $self = new self();
        $self->metadata = $arr;
        return $self;
    }

    private function __construct()
    {
        $this->metadata = [];
    }

    public function addWellType(WellType $wellType): Metadata
    {
        $this->metadata['well_type'] = $wellType->toString();
        return $this;
    }

    public function toArray(): array
    {
        return $this->metadata;
    }
}
