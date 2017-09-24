<?php

namespace Inowas\Soilmodel\Infrastructure\Projection\SoilmodelList;

use Doctrine\DBAL\Connection;
use Inowas\Common\Soilmodel\SoilmodelId;
use Inowas\Soilmodel\Infrastructure\Projection\Table;

class SoilmodelFinder
{
    /** @var Connection $connection */
    protected $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }

    public function count(): int
    {

        $result = $this->connection->fetchAssoc(
            sprintf('SELECT count(*) FROM %s;', Table::SOILMODEL_LIST)
        );

        return (int)$result['count'];
    }


    public function getDetailsBySoilmodelId(SoilmodelId $id): ?array
    {

        /* Todo: Implement this */

        $result = $this->connection->fetchAssoc(
            sprintf('SELECT count(*) FROM %s;', Table::SOILMODEL_LIST)
        );

        return array();
    }


}
