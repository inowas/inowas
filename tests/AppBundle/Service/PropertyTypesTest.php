<?php

namespace AppBundle\Tests\Service;

use AppBundle\Entity\PropertyType;
use AppBundle\Service\PropertyTypes;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PropertyTypesTest extends WebTestCase
{
    /** @var  PropertyTypes */
    protected $propertyTypesService;

    public function setUp()
    {
        self::bootKernel();

        $this->propertyTypesService = static::$kernel->getContainer()
            ->get('inowas.propertytypes');
    }

    public function testReturnsPropertyTypeByAbbreviationIfExists(){
        $this->assertTrue($this->propertyTypesService->findOneByAbbreviation('fuzz') instanceof PropertyType);
    }

    public function testReturnsNullIfAbbreviationNotExists(){
        $this->assertTrue($this->propertyTypesService->findOneByAbbreviation('et') instanceof PropertyType);
    }
}