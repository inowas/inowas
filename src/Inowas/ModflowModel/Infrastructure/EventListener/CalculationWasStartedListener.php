<?php

namespace Inowas\ModflowModel\Infrastructure\EventListener;

use Inowas\ModflowModel\Model\AMQP\FlopyCalculationRequest;
use Inowas\ModflowModel\Model\Event\CalculationWasStarted;
use Inowas\ModflowModel\Service\AMQPFlopyCalculation;
use Inowas\ModflowModel\Service\AMQPModflowCalculation;
use Inowas\ModflowModel\Service\ModflowPackagesManager;
use Prooph\Common\Messaging\DomainEvent;

class CalculationWasStartedListener
{
    /** @var  AMQPModflowCalculation */
    private $calculator;

    /** @var  ModflowPackagesManager */
    private $packagesManager;

    /**
     * CalculationWasStartedListener constructor.
     * @param AMQPFlopyCalculation $calculator
     */
    public function __construct(AMQPFlopyCalculation $calculator, ModflowPackagesManager $packagesManager){
        $this->calculator = $calculator;
        $this->packagesManager = $packagesManager;
    }

    public function onCalculationWasStarted(CalculationWasStarted $event): void
    {
        $packages = $this->packagesManager->getPackages($event->calculationId());
        $request = FlopyCalculationRequest::fromParams($event->modelId(), $event->calculationId(), $packages);
        $this->calculator->calculate($request);
    }

    public function onEvent(DomainEvent $e): void
    {
        $handler = $this->determineEventMethodFor($e);
        if (! method_exists($this, $handler)) {
            throw new \RuntimeException(sprintf(
                'Missing event method %s for projector %s',
                $handler,
                get_class($this)
            ));
        }
        $this->{$handler}($e);
    }

    protected function determineEventMethodFor(DomainEvent $e)
    {
        return 'on' . implode(array_slice(explode('\\', get_class($e)), -1));
    }
}
