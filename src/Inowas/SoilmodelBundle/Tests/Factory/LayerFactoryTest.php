<?php

namespace Inowas\Soilmodel\Tests\Factory;

use Inowas\SoilmodelBundle\Model\Layer;
use Inowas\SoilmodelBundle\Factory\LayerFactory;

class LayerFactoryTest extends \PHPUnit_Framework_TestCase
{

    public function setUp(){}

    public function testCreate(){
        $this->assertInstanceOf(Layer::class, LayerFactory::create());
    }

}