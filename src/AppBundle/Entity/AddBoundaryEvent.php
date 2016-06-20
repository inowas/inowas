<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class AddBoundaryEvent extends AbstractEvent
{
    /**
     * @var ModelObject $boundary
     *
     * @ORM\ManyToOne(targetEntity="ModelObject", cascade={"persist", "remove"})
     */
    private $boundary;

    public function __construct(ModelObject $boundary)
    {
        parent::__construct();
        $this->boundary = $boundary;
    }

    /**
     * @return ModelObject
     */
    public function getBoundary()
    {
        return $this->boundary;
    }
}
