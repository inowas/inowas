<?php

namespace Inowas\ModflowModel\Service;

use Inowas\Common\Id\CalculationId;
use Inowas\ModflowModel\Model\ModflowPackages;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ModflowPackagesPersister
{

    /** @var  string */
    private $dataFolder;

    public function __construct(string $dataFolder)
    {
        $this->dataFolder = $dataFolder;

        $fs = new Filesystem();

        try {
            $fs->mkdir($this->dataFolder);
        } catch (IOException $e) {
            echo 'An error occurred while creating your directory at ' .$e->getPath();
        }
    }

    public function load(CalculationId $calculationId): ModflowPackages
    {
        $finder = new Finder();
        $finder->files()->in($this->dataFolder)->name($calculationId->toString().'.pck');

        if ($finder->count() === 0){
            throw new FileNotFoundException(sprintf('File with name %s in directory %s not found.', $calculationId->toString(), $this->dataFolder));
        }

        $content = '';
        foreach ($finder as $file) {
            $content = $file->getContents();
        }

        return unserialize($content, [ModflowPackages::class]);
    }

    public function save(ModflowPackages $packages): CalculationId
    {
        $hash = $packages->hash();
        $fs = new FileSystem();
        $fs->dumpFile(sprintf('%s/%s/%s.pck', $this->dataFolder, 'packages', $hash), serialize($packages));

        return CalculationId::fromString($hash);
    }

    public function clear(): void
    {
        $fs = new FileSystem();
        if (file_exists(sprintf('%s/%s', $this->dataFolder, 'packages'))) {
            $fs->remove(sprintf('%s/%s', $this->dataFolder, 'packages'));
        }
    }
}
