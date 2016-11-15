<?php

namespace Inowas\Soilmodel\Tests\Model;

use Inowas\Soilmodel\Exception\InvalidArgumentException;
use Inowas\Soilmodel\Model\PropertyType;

class SoilmodelPropertyTypeTest extends \PHPUnit_Framework_TestCase
{
    public function setUp(){}

    public function testInstantiateWithKnownTypeReturnsSoilmodelPropertyType(){
        $this->assertInstanceOf(PropertyType::class, PropertyType::fromString('kx'));
    }

    public function testInstantiateWithUnknownTypeThrowsException(){
        $this->setExpectedException(InvalidArgumentException::class);
        $this->assertInstanceOf(PropertyType::class, PropertyType::fromString('foo'));
    }
}