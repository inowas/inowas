<?php
/**
 * Package ModflowOc
 *
 * The list is used for every stress period and time step after the
 * (IPEROC, ITSOC) tuple until a (IPEROC, ITSOC) tuple is entered with
 * and empty list.
 */
declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;


class OcStressPeriodData implements \JsonSerializable
{
    /** @var array */
    private $data = [];

    public static function create(): OcStressPeriodData
    {
        return new self();
    }

    public static function fromArray(array $data): OcStressPeriodData
    {
        $self = new self();
        $self->data = $data;
        return $self;
    }

    private function __construct(){}

    public function addStressPeriod(OcStressPeriod $ocStressPeriod): OcStressPeriodData
    {
        $this->data[] = $ocStressPeriod->toArray();
        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
