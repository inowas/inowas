<?php

namespace Inowas\ModflowModel\Infrastructure\Projection\Soilmodel;

use Doctrine\DBAL\Connection;
use Inowas\Common\Grid\Nlay;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Modflow\Botm;
use Inowas\Common\Modflow\Chani;
use Inowas\Common\Modflow\Constantcv;
use Inowas\Common\Modflow\Hani;
use Inowas\Common\Modflow\Hdry;
use Inowas\Common\Modflow\Hk;
use Inowas\Common\Modflow\Ihdwet;
use Inowas\Common\Modflow\Ipakcb;
use Inowas\Common\Modflow\Iphdry;
use Inowas\Common\Modflow\Iwetit;
use Inowas\Common\Modflow\Layavg;
use Inowas\Common\Modflow\Laytyp;
use Inowas\Common\Modflow\Layvka;
use Inowas\Common\Modflow\Laywet;
use Inowas\Common\Modflow\Nocvcorrection;
use Inowas\Common\Modflow\Novfc;
use Inowas\Common\Modflow\Ss;
use Inowas\Common\Modflow\Storagecoefficient;
use Inowas\Common\Modflow\Sy;
use Inowas\Common\Modflow\Thickstrt;
use Inowas\Common\Modflow\Top;
use Inowas\Common\Modflow\Vka;
use Inowas\Common\Modflow\Vkcb;
use Inowas\Common\Modflow\Wetdry;
use Inowas\Common\Modflow\Wetfct;
use Inowas\Common\Soilmodel\Layer;
use Inowas\Common\Soilmodel\LayerCollection;
use Inowas\Common\Soilmodel\LayerId;
use Inowas\Common\Soilmodel\Soilmodel;
use Inowas\Common\Soilmodel\SoilmodelQuery;
use Inowas\ModflowModel\Infrastructure\Projection\Table;
use Inowas\ModflowModel\Model\Exception\SqlQueryException;
use Inowas\ModflowModel\Service\LayersPersister;

class SoilmodelFinder
{
    /** @var Connection $connection */
    private $connection;

    /** @var  LayersPersister */
    private $layersPersister;

    /** @var  array */
    private $layers;

    /** @var  Soilmodel */
    private $soilmodel;

    public function __construct(Connection $connection, LayersPersister $layersPersister) {
        $this->connection = $connection;
        $this->layersPersister = $layersPersister;
        $this->connection->setFetchMode(\PDO::FETCH_ASSOC);
    }

    public function getLaytyp(ModflowId $modelId): Laytyp
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $arr = [];
        /** @var Layer $layer */
        foreach ($layers as $key => $layer) {
            $arr[$key] = $layer->laytyp()->toInt();
        }

        return Laytyp::fromArray($arr);
    }

    public function getLayavg(ModflowId $modelId): Layavg
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $arr = [];

        /** @var Layer $layer */
        foreach ($layers as $key => $layer) {
            $arr[$key] = $layer->layavg()->toInt();
        }

        return Layavg::fromArray($arr);
    }

    public function getChani(ModflowId $modelId): Chani
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $arr = [];

        /** @var Layer $layer */
        foreach ($layers as $key => $layer) {
            $arr[$key] = $layer->chani()->toValue();
        }

        return Chani::fromArray($arr);
    }

    public function getLayvka(ModflowId $modelId): Layvka
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $arr = [];

        /** @var Layer $layer */
        foreach ($layers as $key => $layer) {
            $arr[$key] = $layer->layvka()->toValue();
        }

        return Layvka::fromArray($arr);
    }

    public function getLaywet(ModflowId $modelId): Laywet
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $arr = [];

        /** @var Layer $layer */
        foreach ($layers as $key => $layer) {
            $arr[$key] = $layer->laywet()->toFloat();
        }

        return Laywet::fromArray($arr);
    }

    public function getTop(ModflowId $modelId): Top
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);

        /** @var Layer $layer */
        $layer = $layers[0];
        return $layer->top();
    }

    public function getBotm(ModflowId $modelId): Botm
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $arr = [];

        /** @var Layer $layer */
        foreach ($layers as $key => $layer) {
            $arr[$key] = $layer->botm()->toValue();
        }

        return Botm::fromValue($arr);
    }

    public function getHk(ModflowId $modelId): Hk
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $arr = [];

        /** @var Layer $layer */
        foreach ($layers as $key => $layer) {
            $arr[$key] = $layer->hk()->toValue();
        }

        return Hk::from3DArray($arr);
    }

    public function getHani(ModflowId $modelId): Hani
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $arr = [];

        /** @var Layer $layer */
        foreach ($layers as $key => $layer) {
            $arr[$key] = $layer->hani()->toValue();
        }

        return Hani::fromValue($arr);
    }

    public function getVka(ModflowId $modelId): Vka
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $arr = [];

        /** @var Layer $layer */
        foreach ($layers as $key => $layer) {
            $arr[$key] = $layer->vka()->toValue();
        }

        return Vka::fromValue($arr);
    }

    public function getSs(ModflowId $modelId): Ss
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $arr = [];

        /** @var Layer $layer */
        foreach ($layers as $key => $layer) {
            $arr[$key] = $layer->ss()->toValue();
        }

        return Ss::from3DArray($arr);
    }

    public function getSy(ModflowId $modelId): Sy
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $arr = [];

        /** @var Layer $layer */
        foreach ($layers as $key => $layer) {
            $arr[$key] = $layer->sy()->toValue();
        }


        return Sy::from3DArray($arr);
    }

    public function getNlay(ModflowId $modelId): Nlay
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        return Nlay::fromInt(count($layers));
    }

    public function getIpakcb(ModflowId $modelId): Ipakcb
    {
        return $this->getSoilmodel($modelId)->ipakcb();
    }

    public function getHdry(ModflowId $modelId): Hdry
    {
        return $this->getSoilmodel($modelId)->hdry();
    }

    public function getWetfct(ModflowId $modelId): Wetfct
    {
        return $this->getSoilmodel($modelId)->wetfct();
    }

    public function getIhdwet(ModflowId $modelId): Ihdwet
    {
        return $this->getSoilmodel($modelId)->ihdwet();
    }

    public function getIwetit(ModflowId $modelId): Iwetit
    {
        return $this->getSoilmodel($modelId)->iwetit();
    }

    public function getVkcb(ModflowId $modelId): Vkcb
    {
        return $this->getSoilmodel($modelId)->vkcb();
    }

    public function getStoragecoefficient(ModflowId $modelId): Storagecoefficient
    {
        return $this->getSoilmodel($modelId)->storagecoeficient();
    }

    public function getConstantcv(ModflowId $modelId): Constantcv
    {
        return $this->getSoilmodel($modelId)->constantcv();
    }

    public function getThickstrt(ModflowId $modelId): Thickstrt
    {
        return $this->getSoilmodel($modelId)->thickstrt();
    }

    public function getNocvcorrection(ModflowId $modelId): Nocvcorrection
    {
        return $this->getSoilmodel($modelId)->nocvcorrection();
    }

    public function getNovfc(ModflowId $modelId): Novfc
    {
        return $this->getSoilmodel($modelId)->novfc();
    }

    public function getWetdry(ModflowId $modelId): Wetdry
    {
        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $arr = [];

        /** @var Layer $layer */
        foreach ($layers as $key => $layer) {
            $arr[$key] = $layer->wetdry()->toValue();
        }

        return Wetdry::from3DArray($arr);
    }

    public function getIphdry(ModflowId $modelId): Iphdry
    {
        return $this->getSoilmodel($modelId)->iphdry();
    }

    public function findLayer(ModflowId $modelId, LayerId $layerId): ?Layer
    {
        $result = $this->connection->fetchAssoc(
            sprintf('SELECT hash FROM %s WHERE model_id = :model_id AND layer_id = :layer_id', Table::SOILMODEL_LAYERS_LIST),
            ['model_id' => $modelId->toString(), 'layer_id' => $layerId->toString()]
        );

        if (false === $result){
            return null;
        }

        return $this->layersPersister->load($result['hash']);
    }

    public function getSoilmodelQuery(ModflowId $modelId): ?SoilmodelQuery
    {

        $soilmodel = $this->getSoilmodel($modelId);
        if (! $soilmodel instanceof Soilmodel) {
            return null;
        }

        $layers = $this->getLayersSortedByLayerNumber($modelId);
        $layerCollection = LayerCollection::create();
        foreach ($layers as $layer) {
            $layerCollection->addLayer($layer);
        }

        return SoilmodelQuery::create($soilmodel, $layerCollection);
    }

    public function getSoilmodel(ModflowId $modelId): Soilmodel
    {
        if (! $this->soilmodel instanceof Soilmodel) {

            $result = $this->connection->fetchAssoc(
                sprintf('SELECT soilmodel from %s WHERE model_id = :model_id', Table::SOILMODELS),
                ['model_id' => $modelId->toString()]
            );

            if (false === $result) {
                throw SqlQueryException::withClassName(__CLASS__, __FUNCTION__);
            }

            $this->soilmodel = Soilmodel::fromArray(json_decode($result['soilmodel'], true));
        }

        return $this->soilmodel;
    }

    public function getLayers(ModflowId $modflowId): array
    {
        return $this->getLayersSortedByLayerNumber($modflowId);
    }

    private function getLayersSortedByLayerNumber(ModflowId $modelId): array
    {
        if (null === $this->layers) {

            $rows = $this->connection->fetchAll(
                sprintf('SELECT hash from %s WHERE model_id = :model_id ORDER BY layer_number', Table::SOILMODEL_LAYERS_LIST),
                ['model_id' => $modelId->toString()]
            );

            if (false === $rows) {
                throw SqlQueryException::withClassName(__CLASS__, __FUNCTION__);
            }

            $layers = [];
            foreach ($rows as $row) {
                $layers[] = $this->layersPersister->load($row['hash']);
            }

            $this->layers = $layers;
        }

        return $this->layers;
    }
}
