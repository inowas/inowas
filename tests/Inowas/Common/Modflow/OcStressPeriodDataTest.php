<?php

namespace Tests\Inowas\Common\Modflow;

use Inowas\ModflowModel\Model\Packages\OcStressPeriod;
use Inowas\ModflowModel\Model\Packages\OcStressPeriodData;

class OcStressPeriodDataTest extends \PHPUnit_Framework_TestCase
{

    public function test_correct_output(): void
    {
        $ocStressPeriodData = OcStressPeriodData::create();
        $ocStressPeriodData = $ocStressPeriodData->addStressPeriod(OcStressPeriod::fromParams(0,1,['save head']));
        $arr = json_decode(json_encode($ocStressPeriodData));
        $this->assertTrue(is_array($arr));
        $this->assertCount(1, $arr);
        $sp = $arr[0];
        $this->assertObjectHasAttribute('stressPeriod', $sp);
        $this->assertObjectHasAttribute('timeStep', $sp);
        $this->assertObjectHasAttribute('type', $sp);

        $this->assertEquals(0, $sp->stressPeriod);
        $this->assertEquals(1, $sp->timeStep);
        $this->assertTrue(is_array($sp->type));
        $this->assertEquals(['save head'], $sp->type);
    }
}
