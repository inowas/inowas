<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="geological_units")
 */
class GeologicalUnit extends ModelObject
{
    /**
     * @var $elevation
     *
     * @ORM\Column(name="top_elevation", type="float", nullable=true)
     */
    private $topElevation;

    /**
     * @var $elevation
     *
     * @ORM\Column(name="bottom_elevation", type="float", nullable=true)
     */
    private $bottomElevation;

    /**
     * @var GeologicalPoint
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\GeologicalPoint", inversedBy="geologicalUnits", cascade={"persist"})
     * @JMS\MaxDepth(1)
     */
    private $geologicalPoint;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\GeologicalLayer", mappedBy="geologicalUnits")
     * @JMS\MaxDepth(3)
     */
    private $geologicalLayer;

    public function __construct(User $owner = null, Project $project = null, $public = false)
    {
        parent::__construct($owner, $project, $public);
        $this->geologicalLayer = new ArrayCollection();
    }

    /**
     * Set topElevation
     *
     * @param float $topElevation
     * @return GeologicalUnit
     */
    public function setTopElevation($topElevation)
    {
        $this->topElevation = $topElevation;

        return $this;
    }

    /**
     * Get topElevation
     *
     * @return float 
     */
    public function getTopElevation()
    {
        return $this->topElevation;
    }

    /**
     * Set bottomElevation
     *
     * @param float $bottomElevation
     * @return GeologicalUnit
     */
    public function setBottomElevation($bottomElevation)
    {
        $this->bottomElevation = $bottomElevation;

        return $this;
    }

    /**
     * Get bottomElevation
     *
     * @return float 
     */
    public function getBottomElevation()
    {
        return $this->bottomElevation;
    }

    /**
     * Set geologicalPoint
     *
     * @param \AppBundle\Entity\GeologicalPoint $geologicalPoint
     * @return GeologicalUnit
     */
    public function setGeologicalPoint(GeologicalPoint $geologicalPoint = null)
    {
        $this->geologicalPoint = $geologicalPoint;

        return $this;
    }

    /**
     * Get geologicalPoint
     *
     * @return \AppBundle\Entity\GeologicalPoint 
     */
    public function getGeologicalPoint()
    {
        return $this->geologicalPoint;
    }

    /**
     * Add geologicalLayer
     *
     * @param \AppBundle\Entity\GeologicalLayer $geologicalLayer
     * @return GeologicalUnit
     */
    public function addGeologicalLayer(\AppBundle\Entity\GeologicalLayer $geologicalLayer)
    {
        $this->geologicalLayer[] = $geologicalLayer;

        if (!$geologicalLayer->getGeologicalUnits()->contains($this))
        {
            $geologicalLayer->addGeologicalUnit($this);
        }

        return $this;
    }

    /**
     * Remove geologicalLayer
     *
     * @param \AppBundle\Entity\GeologicalLayer $geologicalLayer
     */
    public function removeGeologicalLayer(\AppBundle\Entity\GeologicalLayer $geologicalLayer)
    {
        $this->geologicalLayer->removeElement($geologicalLayer);

        if ($geologicalLayer->getGeologicalUnits()->contains($this))
        {
            $geologicalLayer->removeGeologicalUnit($this);
        }
    }

    /**
     * Get geologicalLayer
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getGeologicalLayer()
    {
        return $this->geologicalLayer;
    }
}
