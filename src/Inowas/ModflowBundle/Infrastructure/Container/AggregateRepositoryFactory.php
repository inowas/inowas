<?php
/**
 * Created by PhpStorm.
 * User: Ralf
 * Date: 17.07.17
 * Time: 11:39
 */

namespace Inowas\ModflowBundle\Infrastructure\Container;


use Prooph\EventSourcing\Aggregate\AggregateRepository;
use Prooph\EventSourcing\Aggregate\AggregateType;
use Prooph\EventStore\StreamName;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AggregateRepositoryFactory
{
    public static function create(ContainerInterface $container, string $name): AggregateRepository
    {

        $config = $container->getParameter('prooph_event_store_repositories')[$name];

        $repositoryClass = $config['repository_class'];

        if (! class_exists($repositoryClass)) {
            throw new RuntimeException(sprintf('Repository class %s cannot be found', $repositoryClass));
        }

        if (! is_subclass_of($repositoryClass, AggregateRepository::class)) {
            throw new RuntimeException(sprintf('Repository class %s must be a sub class of %s', $repositoryClass, AggregateRepository::class));
        }

        $eventStore = $container->get('prooph_event_store');

        if (is_array($config['aggregate_type'])) {
            $aggregateType = AggregateType::fromMapping($config['aggregate_type']);
        } else {
            $aggregateType = AggregateType::fromAggregateRootClass($config['aggregate_type']);
        }

        $aggregateTranslator = $container->get($config['aggregate_translator']);

        $snapshotStore = isset($config['snapshot_store']) ? $container->get($config['snapshot_store']) : null;

        $streamName = isset($config['stream_name']) ? new StreamName($config['stream_name']) : null;

        $oneStreamPerAggregate = (bool) ($config['one_stream_per_aggregate'] ?? false);

        return new $repositoryClass(
            $eventStore,
            $aggregateType,
            $aggregateTranslator,
            $snapshotStore,
            $streamName,
            $oneStreamPerAggregate
        );
    }
}