<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Model\AMQP;

use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;

class ModflowCalculationRequest implements \JsonSerializable
{
    /** @var  UserId */
    private $userId;

    /** @var  ModflowId */
    private $modelId;

    public static function fromParams(UserId $userId, ModflowId $modelId): ModflowCalculationRequest
    {
        return new self($userId, $modelId);
    }

    public static function fromArray(array $arr): ModflowCalculationRequest
    {
        return new self(UserId::fromString($arr['user_id']), ModflowId::fromString($arr['model_id']));
    }

    private function __construct(UserId $userId, ModflowId $modelId)
    {
        $this->userId = $userId;
        $this->modelId = $modelId;
    }

    public function modelId(): ModflowId
    {
        return $this->modelId;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function toArray(): array
    {
        return array(
            'user_id' => $this->userId->toString(),
            'model_id' => $this->modelId->toString()
        );
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
