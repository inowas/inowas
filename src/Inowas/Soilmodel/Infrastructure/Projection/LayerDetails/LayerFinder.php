<?php

namespace Inowas\Soilmodel\Infrastructure\Projection\LayerDetails;

use Doctrine\DBAL\Connection;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Grid\Nlay;
use Inowas\Common\Modflow\Botm;
use Inowas\Common\Modflow\Chani;
use Inowas\Common\Modflow\Hani;
use Inowas\Common\Modflow\Hk;
use Inowas\Common\Modflow\Layavg;
use Inowas\Common\Modflow\Laytyp;
use Inowas\Common\Modflow\Layvka;
use Inowas\Common\Modflow\Laywet;
use Inowas\Common\Modflow\Ss;
use Inowas\Common\Modflow\Sy;
use Inowas\Common\Modflow\Top;
use Inowas\Common\Modflow\Vka;
use Inowas\Common\Soilmodel\AbstractSoilproperty;
use Inowas\Common\Soilmodel\GeologicalLayerNumber;
use Inowas\Common\Soilmodel\SoilmodelId;
use Inowas\Soilmodel\Infrastructure\Projection\Table;

class LayerFinder
{
    /** @var Connection $connection */
    protected $connection;

    public function __construct(Connection $connection) {
        $this->connection = $connection;
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
    }

    public function getLaytyp(SoilmodelId $soilmodelId): Laytyp
    {
        $layers = $this->getSortedLayerNumbers($soilmodelId);
        $type = 'laytyp';
        $layTypArr = [];
        /** @var LayerNumber $layer */
        foreach ($layers as $layer) {
            $result = $this->getValue($soilmodelId, $type, $layer->toInt());
            if (is_array($result) && array_key_exists($type, $result)){
                $layTypArr[] = json_decode($result[$type]);
            }
        }

        return Laytyp::fromValue($layTypArr);
    }

    public function getLayavg(SoilmodelId $soilmodelId): Layavg
    {
        $layers = $this->getSortedLayerNumbers($soilmodelId);
        $type = 'layavg';
        $layAvgArr = [];
        /** @var LayerNumber $layer */
        foreach ($layers as $layer) {
            $result = $this->getValue($soilmodelId, $type, $layer->toInt());
            if (is_array($result) && array_key_exists($type, $result)){
                $layAvgArr[] = json_decode($result[$type]);
            }
        }

        return Layavg::fromValue($layAvgArr);
    }

    public function getChani(SoilmodelId $soilmodelId): Chani
    {
        $layers = $this->getSortedLayerNumbers($soilmodelId);
        $type = 'chani';
        $arr = [];
        /** @var LayerNumber $layer */
        foreach ($layers as $layer) {
            $result = $this->getValue($soilmodelId, $type, $layer->toInt());
            if (is_array($result) && array_key_exists($type, $result)){
                $arr[] = json_decode($result[$type]);
            }
        }

        return Chani::fromValue($arr);
    }

    public function getLayvka(SoilmodelId $soilmodelId): Layvka
    {
        $layers = $this->getSortedLayerNumbers($soilmodelId);
        $type = 'layvka';
        $arr = [];
        /** @var LayerNumber $layer */
        foreach ($layers as $layer) {
            $result = $this->getValue($soilmodelId, $type, $layer->toInt());
            if (is_array($result) && array_key_exists($type, $result)){
                $arr[] = json_decode($result[$type]);
            }
        }

        return Layvka::fromValue($arr);
    }

    public function getLaywet(SoilmodelId $soilmodelId): Laywet
    {
        $layers = $this->getSortedLayerNumbers($soilmodelId);
        $type = 'laywet';
        $arr = [];
        /** @var LayerNumber $layer */
        foreach ($layers as $layer) {
            $result = $this->getValue($soilmodelId, $type, $layer->toInt());
            if (is_array($result) && array_key_exists($type, $result)){
                $arr[] = json_decode($result[$type]);
            }
        }

        return Laywet::fromValue($arr);
    }

    public function getTop(SoilmodelId $soilmodelId): Top
    {
        $layernumber = 0;
        $type = 'top';

        $result = $this->getValue($soilmodelId, $type, $layernumber);
        if (is_array($result) && array_key_exists($type, $result)){
            return Top::fromValue(
                json_decode($result['top'])
            );
        }

        return null;
    }

    public function getBotm(SoilmodelId $soilmodelId): Botm
    {
        $layers = $this->getSortedLayerNumbers($soilmodelId);
        $type = 'botm';
        $botmArr = [];
        /** @var LayerNumber $layer */
        foreach ($layers as $layer) {
            $result = $this->getValue($soilmodelId, $type, $layer->toInt());
            if (is_array($result) && array_key_exists($type, $result)){
                $botmArr[] = json_decode($result[$type]);
            }
        }

        return Botm::fromValue($botmArr);
    }

    public function getHk(SoilmodelId $soilmodelId): Hk
    {
        $type = 'hk';
        $layers = $this->getSortedLayerNumbers($soilmodelId);

        $hk = [];
        /** @var LayerNumber $layer */
        foreach ($layers as $layer) {
            $result = $this->getValue($soilmodelId, $type, $layer->toInt());
            if (is_array($result) && array_key_exists($type, $result)){
                $hk[] = json_decode($result[$type]);
            }
        }

        return Hk::fromValue($hk);
    }

    public function getHani(SoilmodelId $soilmodelId): Hani
    {
        $layers = $this->getSortedLayerNumbers($soilmodelId);
        $type = 'hani';
        $arr = [];
        /** @var LayerNumber $layer */
        foreach ($layers as $layer) {
            $result = $this->getValue($soilmodelId, $type, $layer->toInt());
            if (is_array($result) && array_key_exists($type, $result)){
                $arr[] = json_decode($result[$type]);
            }
        }

        return Hani::fromValue($arr);
    }

    public function getVka(SoilmodelId $soilmodelId): Vka
    {
        $layers = $this->getSortedLayerNumbers($soilmodelId);
        $type = 'vka';
        $arr = [];
        /** @var LayerNumber $layer */
        foreach ($layers as $layer) {
            $result = $this->getValue($soilmodelId, $type, $layer->toInt());
            if (is_array($result) && array_key_exists($type, $result)){
                $arr[] = json_decode($result[$type]);
            }
        }

        return Vka::fromValue($arr);
    }

    public function getSs(SoilmodelId $soilmodelId): Ss
    {
        $type = 'ss';
        $layers = $this->getSortedLayerNumbers($soilmodelId);

        $ss = [];
        /** @var LayerNumber $layer */
        foreach ($layers as $layer) {
            $result = $this->getValue($soilmodelId, $type, $layer->toInt());
            if (is_array($result) && array_key_exists($type, $result)){
                $ss[] = json_decode($result[$type]);
            }
        }

        return Ss::fromValue($ss);
    }

    public function getSy(SoilmodelId $soilmodelId): Sy
    {
        $type = 'sy';
        $layers = $this->getSortedLayerNumbers($soilmodelId);

        $sy = [];
        /** @var LayerNumber $layer */
        foreach ($layers as $layer) {
            $result = $this->getValue($soilmodelId, $type, $layer->toInt());
            if (is_array($result) && array_key_exists($type, $result)){
                $sy[] = json_decode($result[$type]);
            }
        }

        return Sy::fromValue($sy);
    }

    public function getNlay(SoilmodelId $soilmodelId): Nlay
    {
        $layers = $this->getSortedLayerNumbers($soilmodelId);
        return Nlay::fromInt(count($layers));
    }

    public function getValues(SoilmodelId $soilmodelId, AbstractSoilproperty $prop): ?AbstractSoilproperty
    {

        $layers = $this->getSortedLayerNumbers($soilmodelId);
        if (is_null($layers)){
            return null;
        }

        /** @var \Inowas\Common\Soilmodel\GeologicalLayerNumber $layer */
        foreach ($layers as $layer) {
            $result = $this->getValue($soilmodelId, $prop->identifier(), $layer->toInteger());
            if ($result === false){
                return null;
            }

            if (is_array($result) && array_key_exists('values', $result)){
                $prop->addLayerValue(
                    json_decode($result['values']),
                    $layer
                );
            }
        }

        return $prop;
    }

    private function getValue(SoilmodelId $soilmodelId, string $type, $layernumber)
    {
        return $this->connection->fetchAssoc(
            sprintf('SELECT %s from %s WHERE soilmodel_id = :soilmodel_id AND layer_number = :layer_number', $type, Table::LAYER_DETAILS),
            [
                'soilmodel_id' => $soilmodelId->toString(),
                'type' => $type,
                'layer_number' => $layernumber
            ]
        );
    }

    private function getSortedLayerNumbers(SoilmodelId $soilmodelId): array
    {
        $rows = $this->connection->fetchAll(
            sprintf('SELECT DISTINCT layer_number from %s WHERE soilmodel_id = :soilmodel_id ORDER BY layer_number', Table::LAYER_DETAILS),
            ['soilmodel_id' => $soilmodelId->toString()]
        );

        if (! is_array($rows)){
            return null;
        }

        $layers = [];
        foreach ($rows as $row){
            if (array_key_exists('layer_number', $row)){
                $layers[] = GeologicalLayerNumber::fromInteger($row['layer_number']);
            }
        }
        return $layers;
    }
}
