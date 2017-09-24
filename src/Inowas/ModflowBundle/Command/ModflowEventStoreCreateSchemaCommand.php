<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Command;

use Inowas\Common\Id\UserId;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModflowEventStoreCreateSchemaCommand extends ContainerAwareCommand
{

    /** @var  UserId */
    protected $ownerId;

    protected function configure(): void
    {
        // Name and description for app/console command
        $this
            ->setName('inowas:es:schema:create')
            ->setDescription('Creates the database Schema for the eventStore')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): bool
    {
        $this->createEventStreamsTable();
        $config = $this->getContainer()->getParameter('prooph_event_store_repositories');

        foreach ($config as $repo) {
            $this->getContainer()->get('prooph_event_store')->create(
                new Stream(new  StreamName($repo['stream_name']), new \ArrayIterator())
            );
        }
        return true;
    }

    private function createEventStreamsTable(string $name = 'event_streams'): void
    {
        $connection = $this->getContainer()->get('doctrine.dbal.default_connection');

        $sql = sprintf('CREATE TABLE IF NOT EXISTS %s (
          no BIGSERIAL,
          real_stream_name VARCHAR(150) NOT NULL,
          stream_name CHAR(41) NOT NULL,
          metadata JSONB,
          category VARCHAR(150),
          PRIMARY KEY (no),
          UNIQUE (stream_name)
        )', $name);

        $connection->exec($sql);

        $sql = sprintf('CREATE INDEX on %s (category);', $name);
        $connection->exec($sql);

        $sql = <<<EOF
          CREATE TABLE IF NOT EXISTS projections (
              no BIGSERIAL,
              name VARCHAR(150) NOT NULL,
              position JSONB,
              state JSONB,
              status VARCHAR(28) NOT NULL,
              locked_until CHAR(26),
              PRIMARY KEY (no),
              UNIQUE (name)
        );
EOF;

        $connection->exec($sql);

    }
}

