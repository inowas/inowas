<?php

namespace Tests\AppBundle\Process;


use AppBundle\Process\InterpolationProcess;
use AppBundle\Process\ProcessConfigurationInterface;

class InterpolationProcessTest extends \PHPUnit_Framework_TestCase
{

    /** @var  \PHPUnit_Framework_MockObject_MockObject */
    protected $configurationMock;

    public function setUp()
    {
        $this->configurationMock = $this->getMockBuilder('AppBundle\Process\InterpolationProcessConfiguration')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testInstantiate()
    {
        $this->configurationMock->method('getArguments')->willReturn(array());
        $interpolationProcessBuilder = new InterpolationProcess($this->configurationMock);


        /** @var ProcessConfigurationInterface $configurationMock */
        $this->assertInstanceOf('AppBundle\Process\InterpolationProcess', $interpolationProcessBuilder);
    }
}
