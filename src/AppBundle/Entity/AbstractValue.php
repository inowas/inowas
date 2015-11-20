<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * ModelObject
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Entity
 * @ORM\Table(name="values")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="name", type="string")
 * @ORM\DiscriminatorMap({  "name" = "PropertyValue",
 *                          "timevalue" = "PropertyTimeValue",
 *                          "fixedintervalvalues" = "PropertyFixedIntervalValue"
 * })
 * @JMS\ExclusionPolicy("all")
 */

abstract class AbstractValue
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Expose
     */
    private $id;

    /**
     * @var ArrayCollection Property
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Property", inversedBy="values")
     */
    private $property;
    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set property
     *
     * @param \AppBundle\Entity\Property $property
     * @return AbstractValue
     */
    public function setProperty(Property $property = null)
    {
        $this->property = $property;

        return $this;
    }

    /**
     * Get property
     *
     * @return \AppBundle\Entity\Property 
     */
    public function getProperty()
    {
        return $this->property;
    }
}
