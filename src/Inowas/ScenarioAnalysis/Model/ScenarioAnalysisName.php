<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model;

class ScenarioAnalysisName
{
    /** @var string */
    private $name;

    public static function fromString(string $name): ScenarioAnalysisName
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

    public function sameAs($name): bool
    {
        if (! $name instanceof self) {
            return false;
        }

        return $name->toString() === $this->toString();
    }
}
