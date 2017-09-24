<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Service;

use Inowas\ModflowModel\Model\AMQP\ReadDataRequest;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class AMQPFlopyReadData
{
    
    private $channel;
    private $callback_queue;
    private $response;
    private $corr_id;
    private $routingKey;

    public function __construct(AMQPStreamConnection $connection, string $routingKey)
    {
        $this->routingKey = $routingKey;
        $this->channel = $connection->channel();
        list($this->callback_queue, ,) = $this->channel->queue_declare('', false, false, true, false);
        $this->channel->basic_consume(
            $this->callback_queue, '', false, false, false, false,
            array($this, 'on_response'));
    }

    public function on_response($rep) {
        if($rep->get('correlation_id') == $this->corr_id) {
            $this->response = $rep->body;
        }
    }

    private function call($messageBody) {
        $this->response = null;
        $this->corr_id = uniqid();

        $msg = new AMQPMessage(
            (string) $messageBody,
            array('correlation_id' => $this->corr_id,
                'reply_to' => $this->callback_queue)
        );
        $this->channel->basic_publish($msg, '', $this->routingKey);
        while(!$this->response) {
            $this->channel->wait();
        }
        return $this->response;
    }

    public function read(ReadDataRequest $request): string
    {
        return $this->call(json_encode($request));
    }
}
