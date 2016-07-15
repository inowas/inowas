<?php

namespace Inowas\PythonProcessBundle\Model;

use Symfony\Component\Process\ProcessBuilder;

class PythonProcessFactory
{
    /**
     * @var ProcessWithInputOutputFileInterface
     */
    protected $configuration;

    private function __construct(){}

    public static function create(PythonProcessConfigurationInterface $configuration){
        return new PythonProcess(new ProcessBuilder(), $configuration);
    }
}