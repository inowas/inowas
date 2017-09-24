<?php

declare(strict_types=1);

namespace Inowas\ModflowModel\Infrastructure\Projection\Calculation;

use Doctrine\DBAL\Connection;
use Inowas\Common\Calculation\CalculationMessage;
use Inowas\Common\Calculation\CalculationState;
use Inowas\Common\Calculation\CalculationStateQuery;
use Inowas\Common\Calculation\HeadData;
use Inowas\Common\Calculation\TimeSeriesData;
use Inowas\Common\Grid\Ncol;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Grid\Nrow;
use Inowas\Common\Calculation\ResultType;
use Inowas\Common\DateTime\TotalTime;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\LayerValues;
use Inowas\Common\Modflow\Results;
use Inowas\Common\Modflow\StressPeriods;
use Inowas\Common\Modflow\TotalTimes;
use Inowas\Common\Id\CalculationId;
use Inowas\ModflowModel\Infrastructure\Projection\Table;
use Inowas\ModflowModel\Model\AMQP\ReadDataRequest;
use Inowas\ModflowModel\Model\AMQP\ReadDataResponse;
use Inowas\ModflowModel\Service\AMQPFlopyReadData;

class CalculationResultsFinder
{
    /** @var Connection $connection */
    protected $connection;

    /** @var  AMQPFlopyReadData */
    protected $reader;

    public function __construct(Connection $connection, AMQPFlopyReadData $reader) {
        $this->connection = $connection;
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
        $this->reader = $reader;
    }

    public function getCalculationStateQuery(CalculationId $calculationId): ?CalculationStateQuery
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT calculation_id, state, message FROM %s WHERE calculation_id = :calculation_id', Table::CALCULATIONS),
            array('calculation_id' => $calculationId->toString())
        );

        if ($result === false) {
            return null;
        }

        return CalculationStateQuery::createWithCalculationId(
            $calculationId,
            CalculationState::fromInt((int)$result['state']),
            CalculationMessage::fromString($result['message'])
        );
    }

    public function getCalculationDetailsById(CalculationId $calculationId): ?array
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT calculation_id, state, message FROM %s WHERE calculation_id = :calculation_id', Table::CALCULATIONS),
            array('calculation_id' => $calculationId->toString())
        );

        if ($result === false) {
            return null;
        }
        $result['times'] = $this->getTotalTimesFromCalculationById($calculationId);
        $result['layer_values'] = $this->findLayerValues($calculationId);

        return $result;
    }

    public function getCalculationResults(CalculationId $calculationId): ?Results
    {
        $layerValues = $this->findLayerValues($calculationId);
        if (!$layerValues instanceof LayerValues) {
            return null;
        }

        $totalTimes = $this->getTotalTimesFromCalculationById($calculationId);
        if (!$totalTimes instanceof TotalTimes) {
            return null;
        }

        return Results::create($calculationId, $layerValues, $totalTimes);
    }

    public function getTimesByModelId(ModflowId $modelId, ResultType $type): array
    {
        $calculationId = $this->connection->fetchColumn(
            sprintf('SELECT calculation_id from %s WHERE model_id = :model_id ORDER BY id DESC LIMIT 1', Table::CALCULATIONS),
            ['model_id' => $modelId->toString()]
        );

        if ($calculationId === false){
            return [];
        }

        return $this->getTimes(CalculationId::fromString($calculationId), $type);
    }

    public function getTotalTimesFromCalculationById(CalculationId $calculationId): ?TotalTimes
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT stressperiods from %s WHERE calculation_id = :calculation_id', Table::MODFLOWMODELS),
            ['calculation_id' => $calculationId->toString()]
        );

        if ($result === false){
            return null;
        }

        $stressPeriods =  StressPeriods::createFromJson($result['stressperiods']);
        $startDateTime = $stressPeriods->start();
        $timeUnit = $stressPeriods->timeUnit();

        $result = $this->connection->fetchAssoc(
            sprintf('SELECT heads, drawdowns from %s WHERE calculation_id = :calculation_id', Table::CALCULATIONS),
            ['calculation_id' => $calculationId->toString()]
        );

        if ($result === false){
            return null;
        }

        $heads = json_decode($result['heads'], true);
        $drawdowns = json_decode($result['drawdowns'], true);

        $times = [];
        if (count($heads) > 0){$times = $heads;}
        if (count($drawdowns) > 0){$times = $drawdowns;}
        return TotalTimes::create($startDateTime, $timeUnit, $times);
    }

    public function getTimes(CalculationId $calculationId, ResultType $type): array
    {

        $row = $this->connection->fetchAssoc(
            sprintf('SELECT * from %s WHERE calculation_id = :calculation_id', Table::CALCULATIONS),
            ['calculation_id' => $calculationId->toString()]
        );


        $totims = json_decode($row[$type->toString().'s']);
        $result = [];
        foreach ($totims as $totim){
            $result[] = TotalTime::fromInt($totim);
        }

        return $result;
    }

    public function findLayerValues(CalculationId $calculationId): ?LayerValues
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT number_of_layers, heads, drawdowns from %s WHERE calculation_id = :calculation_id', Table::CALCULATIONS),
            ['calculation_id' => $calculationId->toString()]
        );

        $numberOfLayers = $result['number_of_layers'];
        $drawdowns = $result['drawdowns'];
        $heads = $result['heads'];

        $result = [];

        for ($l = 0; $l<$numberOfLayers; $l++) {
            if (count($heads)>0){
                $result[$l][] =  ResultType::HEAD_TYPE;
            }

            if (count($drawdowns)>0){
                $result[$l][] =  ResultType::DRAWDOWN_TYPE;
            }
        }

        return LayerValues::fromArray($result);
    }

    /**
     * @param CalculationId $calculationId
     * @return array
     */
    public function getFileList(CalculationId $calculationId): array
    {
        $request = ReadDataRequest::forFileList($calculationId);
        $response = ReadDataResponse::fromJson($this->reader->read($request));
        return $response->data();
    }

    /**
     * @param CalculationId $calculationId
     * @param Extension $extension
     * @return string
     */
    public function getFile(CalculationId $calculationId, Extension $extension): string
    {
        $request = ReadDataRequest::forFile($calculationId, $extension);
        $response = ReadDataResponse::fromJson($this->reader->read($request));
        return $response->data()[0];
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param CalculationId $calculationId
     * @param ResultType $type
     * @param LayerNumber $layerNumber
     * @param TotalTime $totalTime
     * @return HeadData
     */
    public function findHeadData(CalculationId $calculationId, ResultType $type, LayerNumber $layerNumber, TotalTime $totalTime): HeadData
    {
        $request = ReadDataRequest::forLayerData(
            $calculationId,
            $type,
            $totalTime,
            $layerNumber
        );

        $response = ReadDataResponse::fromJson($this->reader->read($request));
        return HeadData::from2dArray($response->data());
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param CalculationId $calculationId
     * @param CalculationId $calculationId2
     * @param ResultType $type
     * @param LayerNumber $layerNumber
     * @param TotalTime $totalTime
     * @return HeadData
     * @throws \RuntimeException
     */
    public function findHeadDifference(CalculationId $calculationId, CalculationId $calculationId2, ResultType $type, LayerNumber $layerNumber, TotalTime $totalTime): HeadData
    {
        $request = ReadDataRequest::forLayerData(
            $calculationId,
            $type,
            $totalTime,
            $layerNumber
        );

        $response = ReadDataResponse::fromJson($this->reader->read($request));
        $result1 = HeadData::from2dArray($response->data());

        $request = ReadDataRequest::forLayerData(
            $calculationId2,
            $type,
            $totalTime,
            $layerNumber
        );

        $response = ReadDataResponse::fromJson($this->reader->read($request));
        $result2 = HeadData::from2dArray($response->data());

        return $this->calculateDifferenceResults($result1, $result2);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * @param CalculationId $calculationId
     * @param ResultType $type
     * @param LayerNumber $layerNumber
     * @param Nrow $ny
     * @param Ncol $nx
     * @return TimeSeriesData
     */
    public function findTimeSeries(CalculationId $calculationId, ResultType $type, LayerNumber $layerNumber, Nrow $ny, Ncol $nx): TimeSeriesData
    {
        $request = ReadDataRequest::forTimeSeries(
            $calculationId,
            $type,
            $layerNumber,
            $ny,
            $nx
        );

        $response = ReadDataResponse::fromJson($this->reader->read($request));
        return TimeSeriesData::fromArray($response->data());
    }

    /**
     * @param HeadData $res1
     * @param HeadData $res2
     * @return HeadData
     * @throws \RuntimeException
     */
    private function calculateDifferenceResults(HeadData $res1, HeadData $res2): HeadData
    {
        $arr1 = $res1->toArray();
        $arr2 = $res2->toArray();

        if (! (count($arr1) === count($arr2) && count($arr1[0]) === count($arr2[0]))){
            throw new \RuntimeException('Arrays not in the same range');
        }

        $result = [];
        /** @var array $row */
        foreach ($arr1 as $rowNumber => $row){
            foreach ($row as $colNumber => $value){
                if (null === $arr1[$rowNumber][$colNumber] || null === $arr2[$rowNumber][$colNumber]){
                    $result[$rowNumber][$colNumber] = null;
                    continue;
                }

                $result[$rowNumber][$colNumber] = round($arr1[$rowNumber][$colNumber] - $arr2[$rowNumber][$colNumber], 3);
            }
        }

        return HeadData::from2dArray($result);
    }
}
