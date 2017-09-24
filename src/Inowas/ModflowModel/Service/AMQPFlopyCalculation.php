<?php

namespace Inowas\ModflowModel\Service;

use Inowas\ModflowModel\Model\AMQP\FlopyCalculationRequest;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPFlopyCalculation
{
    private $channel;
    private $connection;
    private $routingKey;

    public function __construct(AMQPStreamConnection $connection, string $routingKey)
    {
        $this->connection = $connection;
        $this->routingKey = $routingKey;
    }

    public function calculate(FlopyCalculationRequest $request): void
    {
        $this->channel = $this->connection->channel();
        $this->channel->queue_declare($this->routingKey, false, true, false, false);

        $msg = new AMQPMessage(
            json_encode($request),
            array('delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
        );
        $this->channel->basic_publish($msg, '', $this->routingKey);
        $this->channel->close();
    }
}
