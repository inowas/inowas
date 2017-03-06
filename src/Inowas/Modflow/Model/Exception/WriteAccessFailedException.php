<?php

namespace Inowas\Modflow\Model\Exception;

use Inowas\Common\Id\UserId;

final class WriteAccessFailedException extends \InvalidArgumentException
{
    public static function withUserAndOwner(UserId $user, UserId $owner)
    {
        return new self(sprintf('User with id=%s is not owner (id=%s) of the project.', $user->toString(), $owner->toString()));
    }
}