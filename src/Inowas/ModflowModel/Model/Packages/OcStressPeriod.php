<?php
/**
 * Package ModflowOc
 *
 * (default is {(0,0):['save head']})
 *
 * The list can have any valid MODFLOW OC print/save option:
 * PRINT HEAD
 * PRINT DRAWDOWN
 * PRINT BUDGET
 * SAVE HEAD
 * SAVE DRAWDOWN
 * SAVE BUDGET
 * SAVE IBOUND
 *
 * The lists can also include (1) DDREFERENCE in the list to reset
 * drawdown reference to the period and step and (2) a list of layers
 * for PRINT HEAD, SAVE HEAD, PRINT DRAWDOWN, SAVE DRAWDOWN, and
 * SAVE IBOUND.
 *
 * The list is used for every stress period and time step after the
 * (IPEROC, ITSOC) tuple until a (IPEROC, ITSOC) tuple is entered with
 * and empty list.
 */
declare(strict_types=1);

namespace Inowas\ModflowModel\Model\Packages;

class OcStressPeriod implements \JsonSerializable
{

    CONST PRINT_HEAD = 'print head';
    CONST PRINT_DRAWDOWN = 'print drawdown';
    CONST PRINT_BUDGET = 'print budget';
    CONST SAVE_HEAD = 'save head';
    CONST SAVE_DRAWDOWN = 'save drawdown';
    CONST SAVE_BUDGET = 'save budget';
    CONST SAVE_IBOUND = 'save ibound';

    /** @var  int */
    protected $stressPeriod;

    /** @var  int */
    protected $timeStep;

    /** @var  array */
    protected $types;

    public static function fromParams(int $stressPeriod, int $timeStep, $types): OcStressPeriod
    {
        $self = new self();
        $self->stressPeriod = $stressPeriod;
        $self->timeStep = $timeStep;

        if (is_array($types)){
            $self->types = $types;
            return $self;
        }

        $self->types = [];
        $self->types[] = $types;
        return $self;
    }

    public static function fromArray(array $arr): OcStressPeriod
    {
        $self = new self();
        $self->stressPeriod = $arr['stressPeriod'];
        $self->timeStep = $arr['timeStep'];
        $self->types = $arr['type'];
        return $self;
    }

    public function toArray(): array
    {
        return array(
            'stressPeriod' => $this->stressPeriod,
            'timeStep' => $this->timeStep,
            'type' => $this->types
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
