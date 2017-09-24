<?php

declare(strict_types=1);

namespace Inowas\Common\Boundaries;

use Inowas\Common\DateTime\DateTime;
use Inowas\Common\Exception\KeyHasUseException;
use Inowas\Common\Exception\KeyInvalidException;

class DateTimeValuesCollection
{
    private $items = [];

    public static function create(): DateTimeValuesCollection
    {
        return new self();
    }

    public static function fromTypeAndArray(BoundaryType $type, array $arr): DateTimeValuesCollection
    {
        $self = new self();

        foreach ($arr as $item) {
            $dateTimeValue = DateTimeValueFactory::createFromArray($type, $item);
            $self->add($dateTimeValue);
        }

        return $self;
    }

    private function __construct()
    {}

    public function add(DateTimeValue $dateTimeValue, $key = null): void
    {
        if (null === $key) {
            $this->items[] = $dateTimeValue;
            return;
        }

        if (isset($this->items[$key])) {
            throw new KeyHasUseException("Key $key already in use.");
        }

        $this->items[$key] = $dateTimeValue;
    }

    public function delete($key): void
    {
        if (isset($this->items[$key])) {
            unset($this->items[$key]);
            return;
        }

        throw new KeyInvalidException("Invalid key $key.");
    }

    public function get($key): DateTimeValue
    {
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        throw new KeyInvalidException("Invalid key $key.");
    }

    public function toArray(): array
    {
        $result = [];

        /** @var DateTimeValue $dateTimeValue */
        foreach ($this->items as $dateTimeValue){
            $result[] = $dateTimeValue->toArray();
        }

        return $result;
    }

    public function toArrayValues(): array
    {
        $result = [];

        /** @var DateTimeValue $dateTimeValue */
        foreach ($this->items as $dateTimeValue){
            $result[] = $dateTimeValue->toArrayValues();
        }

        return $result;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function findValueByDateTime(DateTime $dateTime): ?DateTimeValue
    {
        $values = $this->items;
        usort($values, function ($v1, $v2) {

            /** @var $v1 DateTimeValue */
            $dtV1 = $v1->dateTime();

            /** @var $v2 DateTimeValue */
            $dtV2 = $v2->dateTime();

            return ($dtV1 < $dtV2) ? +1 : -1;
        });

        /** @var DateTimeValue $value */
        foreach ($values as $value) {
            if ($dateTime >= $value->dateTime()){
                return $value;
            }
        }

        return null;
    }

    public function getDateTimes(): array
    {
        $result = [];

        /** @var DateTimeValue $item */
        foreach ($this->items as $item) {
            $result[] = $item->dateTime();
        }

        return $result;
    }
}
