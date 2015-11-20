<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity()
 * @ORM\Table(name="property_values")
 * @JMS\ExclusionPolicy("all")
 */
class PropertyValue extends AbstractValue
{
    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float")
     */
    private $value;


    /**
     * Set value
     *
     * @param float $value
     * @return PropertyValue
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float 
     */
    public function getValue()
    {
        return $this->value;
    }
}