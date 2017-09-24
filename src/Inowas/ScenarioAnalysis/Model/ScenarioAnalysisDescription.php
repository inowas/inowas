<?php

declare(strict_types=1);

namespace Inowas\ScenarioAnalysis\Model;

class ScenarioAnalysisDescription
{
    /** @var string */
    private $description;

    public static function fromString(string $description): ScenarioAnalysisDescription
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

    public function sameAs($name): bool
    {
        if (! $name instanceof self) {
            return false;
        }

        return $name->toString() === $this->toString();
    }
}
