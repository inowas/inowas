<?php

declare(strict_types=1);

namespace Inowas\Tool\Model\Handler;

use Inowas\ModflowBundle\Exception\AccessDeniedException;
use Inowas\Tool\Model\Command\DeleteToolInstance;
use Inowas\Tool\Model\ToolInstanceAggregate;
use Inowas\Tool\Model\ToolInstanceList;

final class DeleteToolInstanceHandler
{

    /** @var  ToolInstanceList */
    private $toolInstanceList;

    public function __construct(ToolInstanceList $toolInstanceList)
    {
        $this->toolInstanceList = $toolInstanceList;
    }

    public function __invoke(DeleteToolInstance $command)
    {
        /** @var ToolInstanceAggregate $toolInstance */
        $toolInstance = $this->toolInstanceList->get($command->id());

        if (! $toolInstance->userId()->sameValueAs($command->userId())) {
            throw AccessDeniedException::withMessage(
                sprintf('User with id=%s does not have sufficient access to tool with id=%s',
                    $command->userId()->toString(), $command->id()->toString())
            );
        }

        $toolInstance->delete($command->userId());

        $this->toolInstanceList->save($toolInstance);
    }
}
