<?php

declare(strict_types=1);

namespace Inowas\Tool\Infrastructure\Projection;

use Doctrine\DBAL\Connection;
use Inowas\Common\Id\UserId;
use Inowas\Tool\Model\ToolType;
use Inowas\Tool\Model\ToolId;

class ToolFinder
{
    /** @var Connection $connection */
    protected $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
    }

    public function findPublic(): array
    {
        $results = $this->connection->fetchAll(
            sprintf('SELECT id, name, description, project, application, tool, created_at, user_id, user_name, created_at, public FROM %s WHERE public = 1', Table::TOOL_LIST)
        );

        if ($results === false) {
            $results = [];
        }

        return $results;
    }

    public function findPublicByType(ToolType $toolType): array
    {
        $results = $this->connection->fetchAll(
            sprintf('SELECT id, name, description, project, application, tool, created_at, user_id, user_name, created_at, public FROM %s WHERE public = 1 AND tool = :tool', Table::TOOL_LIST),
            ['tool' => $toolType->toString()]
        );

        if ($results === false) {
            $results = [];
        }

        return $results;
    }

    public function findByUserId(UserId $userId): array
    {
        $results = $this->connection->fetchAll(
            sprintf('SELECT id, name, description, project, application, tool, created_at, user_id, user_name, created_at, public FROM %s WHERE user_id = :user_id', Table::TOOL_LIST),
            ['user_id' => $userId->toString()]
        );

        if ($results === false) {
            $results = [];
        }

        return $results;
    }

    public function findById(ToolId $id): ?array
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT id, name, description, project, application, tool, created_at, user_id, user_name, created_at, public FROM %s WHERE id = :id', Table::TOOL_LIST),
            ['id' => $id->toString()]
        );

        if ($result === false) {
            return null;
        }

        return $result;
    }

    public function findByUserIdAndType(UserId $userId, ToolType $toolType): array
    {
        $results = $this->connection->fetchAll(
            sprintf('SELECT id, name, description, project, application, tool, created_at, user_id, user_name, created_at, public FROM %s WHERE user_id = :user_id AND tool = :tool', Table::TOOL_LIST),
            ['user_id' => $userId->toString(), 'tool' => $toolType->toString()]
        );

        if ($results === false) {
            $results = [];
        }

        return $results;
    }

    public function getToolTypeById(ToolId $id): ?ToolType
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT tool FROM %s WHERE id = :id', Table::TOOL_LIST),
            ['id' => $id->toString()]
        );

        if ($result === false) {
            return null;
        }

        return ToolType::fromString($result['tool']);
    }

    public function isPublic(ToolId $toolId): bool
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT public FROM %s WHERE id = :id', Table::TOOL_LIST),
            ['id' => $toolId->toString()]
        );

        if ($result === false) {
            return false;
        }

        return $result['public'] === 1;
    }

    public function isToolOwner(ToolId $toolId, UserId $userId): bool
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT count(user_id) FROM %s WHERE id = :id AND user_id = :user_id', Table::TOOL_LIST),
            ['id' => $toolId->toString(), 'user_id' => $userId->toString()]
        );

        return $result['count'] > 0;
    }

    public function canBeClonedByUser(ToolId $toolId, UserId $userId): bool
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT count(user_id) FROM %s WHERE (id = :id AND user_id = :user_id) OR (id = :id AND public = :public)', Table::TOOL_LIST),
            ['id' => $toolId->toString(), 'user_id' => $userId->toString(), 'public' => 1]
        );

        return $result['count'] > 0;
    }

    public function canBeDeletedByUser(ToolId $toolId, UserId $userId): bool
    {
        return $this->isToolOwner($toolId, $userId);
    }
}
