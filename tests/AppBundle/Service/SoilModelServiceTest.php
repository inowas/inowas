<?php

namespace Tests\AppBundle\Service;

use AppBundle\Entity\GeologicalLayer;
use AppBundle\Model\GeologicalLayerFactory;
use AppBundle\Model\PropertyFactory;
use AppBundle\Model\PropertyType;
use AppBundle\Model\PropertyTypeFactory;
use AppBundle\Model\PropertyValueFactory;
use AppBundle\Service\SoilModelService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SoilModelServiceTest extends WebTestCase
{

    /** @var  SoilModelService */
    protected $soilModelService;

    /** @var  GeologicalLayer */
    protected $geologicalLayer;
    
    /** @var  PropertyType */
    protected $propertyType;

    public function setUp()
    {
        self::bootKernel();
        $this->soilModelService = static::$kernel->getContainer()
            ->get('inowas.soilmodel');

        $this->propertyType = PropertyTypeFactory::create(PropertyType::TOP_ELEVATION);
        
        $this->geologicalLayer = GeologicalLayerFactory::create()
            ->setName('L1')
            ->setOrder(GeologicalLayer::TOP_LAYER)
            ->addProperty(PropertyFactory::create()
                ->setPropertyType($this->propertyType)
            )
            ->addValue($this->propertyType, PropertyValueFactory::create()
                ->setValue('1234.44')
            )
        ;
    }
    
    public function test()
    {
        $this->assertTrue(true);
    }

}