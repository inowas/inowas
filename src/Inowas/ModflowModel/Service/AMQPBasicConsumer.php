<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Service;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class AMQPBasicConsumer
{

    private $connection;
    private $routingKey;

    public function __construct(AMQPStreamConnection $connection, string $routingKey)
    {
        $this->connection = $connection;
        $this->routingKey = $routingKey;
    }

    public function listen(callable $callback)
    {
        $channel = $this->connection->channel();
        $channel->queue_declare($this->routingKey, false, true, false, false);
        echo ' [*] Waiting for messages. To exit press CTRL+C', "\n";

        $channel->basic_qos(null, 1, null);
        $channel->basic_consume($this->routingKey, '', false, false, false, false, $callback);

        while(count($channel->callbacks)) {
            $channel->wait();
        }
        
        $channel->close();
    }
}
