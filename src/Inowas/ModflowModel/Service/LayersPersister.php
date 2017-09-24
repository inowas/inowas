<?php

namespace Inowas\ModflowModel\Service;

use Inowas\Common\Soilmodel\Layer;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class LayersPersister
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

    public function load(string $hash): Layer
    {
        $finder = new Finder();
        $finder->files()->in($this->dataFolder)->name($hash);

        if ($finder->count() === 0){
            throw new FileNotFoundException(sprintf('File with name %s in directory %s not found.', $hash, $this->dataFolder));
        }

        $content = '';
        foreach ($finder as $file) {
            $content = $file->getContents();
        }

        return Layer::fromArray(json_decode($content, true));
    }

    public function save(Layer $layer): string
    {
        $hash = $layer->hash();
        $fs = new FileSystem();
        $fs->dumpFile(sprintf('%s/%s/%s', $this->dataFolder, 'layers', $hash), json_encode($layer->toArray()));
        
        return $hash;
    }

    public function clear(): void
    {
        $fs = new FileSystem();
        if (file_exists(sprintf('%s/%s', $this->dataFolder, 'layers'))) {
            $fs->remove(sprintf('%s/%s', $this->dataFolder, 'layers'));
        }
    }
}
