<?php

namespace Inowas\Tool\Model;

final class ToolData
{

    /** @var array */
    private $data;

    public static function create(): ToolData
    {
        return new self([]);
    }

    public static function fromArray(array $data): ToolData
    {
        return new self($data);
    }

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
