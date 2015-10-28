<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * TimeSeries
 *
 * @ORM\Table(name="inowas_time_series")
 * @ORM\Entity
 */
class TimeSeries
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var ArrayCollection ModelObjectProperty
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ModelObjectProperty", inversedBy="timeSeries")
     */
    private $modelObjectProperties;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timeStamp", type="datetimetz", nullable=true)
     */
    private $timeStamp=null;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float")
     */
    private $value;

    /**
     * @var Raster
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Raster")
     * @ORM\JoinColumn(name="raster_id", referencedColumnName="rid")
     */
    private $raster;

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
     * Set timeStamp
     *
     * @param \DateTime $timeStamp
     * @return TimeSeries
     */
    public function setTimeStamp($timeStamp)
    {
        $this->timeStamp = $timeStamp;

        return $this;
    }

    /**
     * Get timeStamp
     *
     * @return \DateTime 
     */
    public function getTimeStamp()
    {
        return $this->timeStamp;
    }

    /**
     * Set value
     *
     * @param float $value
     * @return TimeSeries
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

    /**
     * Set modelObjectProperties
     *
     * @param \AppBundle\Entity\ModelObjectProperty $modelObjectProperties
     * @return TimeSeries
     */
    public function setModelObjectProperties(ModelObjectProperty $modelObjectProperties = null)
    {
        $this->modelObjectProperties = $modelObjectProperties;
        return $this;
    }

    /**
     * Get modelObjectProperties
     *
     * @return \AppBundle\Entity\ModelObjectProperty 
     */
    public function getModelObjectProperties()
    {
        return $this->modelObjectProperties;
    }

    /**
     * Set raster
     *
     * @param \AppBundle\Entity\Raster $raster
     * @return TimeSeries
     */
    public function setRaster(\AppBundle\Entity\Raster $raster = null)
    {
        $this->raster = $raster;

        return $this;
    }

    /**
     * Get raster
     *
     * @return \AppBundle\Entity\Raster 
     */
    public function getRaster()
    {
        return $this->raster;
    }
}
