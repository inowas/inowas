<?php

declare(strict_types=1);

namespace Inowas\Tool\Model\Handler;

use Inowas\Tool\Model\Command\CreateToolInstance;
use Inowas\Tool\Model\ToolInstanceAggregate;
use Inowas\Tool\Model\ToolInstanceList;

final class CreateToolInstanceHandler
{

    /** @var  ToolInstanceList */
    private $toolInstanceList;

    public function __construct(ToolInstanceList $toolInstanceList)
    {
        $this->toolInstanceList = $toolInstanceList;
    }

    public function __invoke(CreateToolInstance $command)
    {
        $toolInstance = ToolInstanceAggregate::create(
            $command->id(),
            $command->userId(),
            $command->type()
        );

        $toolInstance->updateName($command->userId(), $command->name());
        $toolInstance->updateDescription($command->userId(), $command->description());
        $toolInstance->updateData($command->userId(), $command->data());

        $this->toolInstanceList->save($toolInstance);
    }
}
