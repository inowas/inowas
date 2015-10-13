<?php

namespace AppBundle\Entity;

use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="inowas_observation_point")
 */
class ObservationPoint extends ModelObject
{

    /**
     * @var Point
     *
     * @ORM\Column(name="point", type="point", nullable=true)
     */
    private $point;

    /**
     * @var $elevation
     *
     * @ORM\Column(name="elevation", type="float", nullable=true)
     */
    private $elevation;

    /**
     * @var ArrayCollection ModelObject
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\ModelObject", mappedBy="observationPoints")
     */
    private $modelObjects;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->modelObjects = new ArrayCollection();
    }

    /**
     * Set point
     *
     * @param point $point
     * @return ObservationPoint
     */
    public function setPoint($point)
    {
        $this->point = $point;

        return $this;
    }

    /**
     * Get point
     *
     * @return point 
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * Set elevation
     *
     * @param float $elevation
     * @return ObservationPoint
     */
    public function setElevation($elevation)
    {
        $this->elevation = $elevation;

        return $this;
    }

    /**
     * Get elevation
     *
     * @return float 
     */
    public function getElevation()
    {
        return $this->elevation;
    }

    /**
     * Add modelObjects
     *
     * @param \AppBundle\Entity\ModelObject $modelObjects
     * @return ObservationPoint
     */
    public function addModelObject(ModelObject $modelObjects)
    {
        $this->modelObjects[] = $modelObjects;
        $modelObjects->addObservationPoint($this);

        return $this;
    }

    /**
     * Remove modelObjects
     *
     * @param \AppBundle\Entity\ModelObject $modelObjects
     */
    public function removeModelObject(ModelObject $modelObjects)
    {
        $this->modelObjects->removeElement($modelObjects);
        $modelObjects->removeObservationPoint($this);
    }

    /**
     * Get modelObjects
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getModelObjects()
    {
        return $this->modelObjects;
    }
}
