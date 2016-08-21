<?php

namespace Inowas\PyprocessingBundle\Model\Modflow\Package;

use AppBundle\Entity\ModFlowModel;

class MfPackageAdapter
{

    /** @var  ModFlowModel */
    protected $model;

    /**
     * @return string
     */
    public function getModelname(): string
    {
        return $this->model->getSanitizedName();
    }

    /**
     * @return string
     */
    public function getNamefileExt(): string
    {
        return 'nam';
    }

    /**
     * @return string
     */
    public function getVersion(): string
    {
        return 'mf2005';
    }

    /**
     * @return string
     */
    public function getExeName(): string
    {
        return 'mf2005';
    }

    /**
     * @return boolean
     */
    public function isStructured(): bool
    {
        return true;
    }

    /**
     * @return int
     */
    public function getListunit(): int
    {
        return 2;
    }

    /**
     * @return string
     */
    public function getModelWs(): string
    {
        return './ascii';
    }

    /**
     * @return null
     */
    public function getExternalPath()
    {
        return null;
    }

    /**
     * @return boolean
     */
    public function isVerbose(): bool
    {
        return false;
    }

    /**
     * @return boolean
     */
    public function isLoad(): bool
    {
        return true;
    }

    /**
     * @return int
     */
    public function getSilent(): int
    {
        return 0;
    }

}