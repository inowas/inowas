<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Command;

use Inowas\Common\Id\UserId;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModflowEventStoreTruncateCommand extends ContainerAwareCommand
{

    /** @var  UserId */
    protected $ownerId;

    protected function configure(): void
    {
        // Name and description for app/console command
        $this
            ->setName('inowas:es:truncate')
            ->setDescription('Truncates the event-stream Database and cleans the local modflow-data folder');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getContainer()->getParameter('prooph_event_store_repositories');

        foreach ($config as $repo) {

            try {
                $this->getContainer()->get('prooph_event_store')->delete(new  StreamName($repo['stream_name']));
            }
            catch (\Throwable $e){}

            $this->getContainer()->get('prooph_event_store')->create(
                new Stream(new  StreamName($repo['stream_name']), new \ArrayIterator())
            );
        }

        $this->cleanDataFolder();
    }

    private function cleanDataFolder(): void
    {
        $this->getContainer()->get('inowas.modflowmodel.modflow_packages_persister')->clear();
        $this->getContainer()->get('inowas.modflowmodel.layers_persister')->clear();
    }
}
