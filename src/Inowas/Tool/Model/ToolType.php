<?php

namespace Inowas\Tool\Model;

final class ToolType
{

    const GW_MOUNDING = 'T02';
    const MODEL_SETUP = 'T03';
    const SCENARIOANALYSIS = 'T07';

    public static $availableTypes = array(
        'T02',
        'T03',
        'T07'
    );

    /** @var  string */
    private $type;

    public static function isValid($type): bool
    {
        return in_array($type, self::$availableTypes, true);
    }

    public static function fromString(string $type): ToolType
    {
        return new self($type);
    }

    private function __construct(string $type)
    {
        $this->type = $type;
    }

    public function toString(): string
    {
        return $this->type;
    }

    public function sameAs($value): bool
    {
        if (! $value instanceof self){
            return false;
        }

        return $this->type === $value->toString();
    }
}
