<?php

declare(strict_types=1);

namespace Inowas\Common\Modflow;

final class StressPeriod implements \JsonSerializable
{
    /** @var int  */
    private $totimStart;

    /** @var int  */
    private $perlen;

    /** @var int  */
    private $nstp;

    /** @var float  */
    private $tsmult;

    /** @var bool  */
    private $steady;

    public static function create(int $totimStart, int $perlen, int $nstp, float $tsmult, bool $steady){
        return new self($totimStart, $perlen, $nstp, $tsmult, $steady);
    }

    public static function createFromArray(array $arr): StressPeriod
    {
        return self::create(
            (int)$arr['totim_start'],
            (int)$arr['perlen'],
            (int)$arr['nstp'],
            (float)$arr['tsmult'],
            (bool)$arr['steady']
        );
    }

    public static function isValidArray(array $arr): bool
    {
        if (! array_key_exists('totim_start', $arr)) {
            return false;
        }

        if (! array_key_exists('perlen', $arr)) {
            return false;
        }

        if (! array_key_exists('nstp', $arr)) {
            return false;
        }

        if (! array_key_exists('tsmult', $arr)) {
            return false;
        }

        if (! array_key_exists('steady', $arr)) {
            return false;
        }

        return true;
    }

    private function __construct(int $totimStart, int $perlen, int $nstp, float $tsmult, bool $steady){
        $this->totimStart = $totimStart;
        $this->perlen = $perlen;
        $this->nstp = $nstp;
        $this->tsmult = $tsmult;
        $this->steady = $steady;
    }

    public function totimStart(): int
    {
        return $this->totimStart;
    }

    public function perlen(): int
    {
        return $this->perlen;
    }

    public function nstp(): int
    {
        return $this->nstp;
    }

    public function tsmult(): float
    {
        return $this->tsmult;
    }

    public function steady(): bool
    {
        return $this->steady;
    }

    public function toArray(): array
    {
        return array(
            'totim_start' => $this->totimStart,
            'perlen' => $this->perlen,
            'nstp' => $this->nstp,
            'tsmult' => $this->tsmult,
            'steady' => $this->steady
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
