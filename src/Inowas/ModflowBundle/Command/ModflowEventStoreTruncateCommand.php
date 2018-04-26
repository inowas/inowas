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

    /**
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure(): void
    {
        // Name and description for app/console command
        $this
            ->setName('inowas:es:truncate')
            ->setDescription('Truncates the event-stream Database and cleans the local modflow-data folder');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws \Symfony\Component\DependencyInjection\Exception\InvalidArgumentException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \LogicException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->getContainer()->getParameter('prooph_event_store_repositories');

        /** @var array $config */
        foreach ($config as $repo) {

            try {
                $this->getContainer()->get('prooph_event_store')->delete(new  StreamName($repo['stream_name']));
            }
            catch (\Throwable $e){}

            $this->getContainer()->get('prooph_event_store')->create(
                new Stream(new  StreamName($repo['stream_name']), new \ArrayIterator())
            );
        }

        if ($this->getContainer()->get('kernel')->getEnvironment() === 'prod')
        {
            $this->cleanDataFolder();
        }
    }

    /**
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \LogicException
     */
    private function cleanDataFolder(): void
    {
        $this->getContainer()->get('inowas.modflowmodel.modflow_packages_persister')->clear();
        $this->getContainer()->get('inowas.modflowmodel.layers_persister')->clear();
    }
}
