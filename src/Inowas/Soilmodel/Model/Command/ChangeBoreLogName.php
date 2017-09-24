<?php

declare(strict_types=1);

namespace Inowas\Soilmodel\Model\Command;

use Inowas\Common\Id\UserId;
use Inowas\Common\Soilmodel\BoreLogId;
use Inowas\Common\Soilmodel\BoreLogName;
use Prooph\Common\Messaging\Command;
use Prooph\Common\Messaging\PayloadConstructable;
use Prooph\Common\Messaging\PayloadTrait;

class ChangeBoreLogName extends Command implements PayloadConstructable
{

    use PayloadTrait;

    public static function byUserWithId(UserId $userId, BoreLogId $boreLogId, BoreLogName $name): ChangeBoreLogName
    {
        return new self(
            [
                'user_id' => $userId->toString(),
                'borelog_id' => $boreLogId->toString(),
                'name' => $name->toString()
            ]
        );
    }

    public function userId(): UserId
    {
        return UserId::fromString($this->payload['user_id']);
    }

    public function boreLogId(): BoreLogId
    {
        return BoreLogId::fromString($this->payload['borelog_id']);
    }

    public function name(): BoreLogName
    {
        return BoreLogName::fromString($this->payload['name']);
    }
}
