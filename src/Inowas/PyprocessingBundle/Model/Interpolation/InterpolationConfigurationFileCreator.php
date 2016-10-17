<?php

namespace Inowas\PyprocessingBundle\Model\Interpolation;

use Inowas\PyprocessingBundle\Model\PythonProcess\InputOutputFileInterface;
use Inowas\PyprocessingBundle\Model\PythonProcess\ProcessFile;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Filesystem\Filesystem;

class InterpolationConfigurationFileCreator implements InputOutputFileInterface
{
    /** @var  string */
    protected $tempFolder;

    /** @var  ProcessFile */
    protected $inputFile;

    /** @var  ProcessFile */
    protected $outputFile;

    /**
     * InterpolationConfigurationFileCreator constructor.
     * @param $tempFolder
     */
    public function __construct($tempFolder)
    {
        $this->tempFolder = $tempFolder;
    }

    public function createFiles($algorithm, InterpolationConfiguration $configuration){

        $interpolationParameter = new InterpolationParameter($algorithm, $configuration);
        $interpolationJSON = json_encode($interpolationParameter);

        $randomFileName = Uuid::uuid4()->toString();
        $inputFileName  = $this->tempFolder . '/' . $randomFileName . '.in';
        $outputFileName = $this->tempFolder . '/' . $randomFileName . '.out';

        $fs = new Filesystem();
        $fs->dumpFile($inputFileName, $interpolationJSON);
        $fs->touch($outputFileName);

        $this->inputFile = ProcessFile::fromFilename($inputFileName);
        $this->outputFile = ProcessFile::fromFilename($outputFileName);
    }

    /**
     * @return \Inowas\PyprocessingBundle\Model\PythonProcess\ProcessFile
     */
    public function getInputFile()
    {
        return $this->inputFile;
    }

    /**
     * @return ProcessFile
     */
    public function getOutputFile()
    {
        return $this->outputFile;
    }
}