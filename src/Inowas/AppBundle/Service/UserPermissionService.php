<?php

declare(strict_types=1);

namespace Inowas\AppBundle\Service;

use Inowas\AppBundle\Model\UserPermission;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\ModflowModel\Infrastructure\Projection\ModelList\ModelFinder;

class UserPermissionService
{

    /** @var  ModelFinder */
    private $modelFinder;

    public function __construct(ModelFinder $modelFinder)
    {
        $this->modelFinder = $modelFinder;
    }

    public function getModelPermissions(UserId $userId, ModflowId $modelId): UserPermission
    {
        if ($this->modelFinder->userHasWriteAccessToModel($userId, $modelId)){
            return UserPermission::readWriteExecute();
        }

        if ($this->modelFinder->userHasReadAccessToModel($userId, $modelId)) {
            return UserPermission::readOnly();
        }

        return UserPermission::noPermission();
    }
}
