<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Ramsey\Uuid\Uuid;

/**
 * ModFlowKernel
 * 
 * @ORM\Entity()
 * @JMS\ExclusionPolicy("all")
 */
class ModFlowKernel extends AbstractKernel
{
    public function __construct()
    {
        parent::__construct();
    }
}
