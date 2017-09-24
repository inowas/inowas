<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Inowas\Common\Calculation\HeadData;
use Inowas\Common\Calculation\ResultType;
use Inowas\Common\Calculation\TimeSeriesData;
use Inowas\Common\DateTime\TotalTime;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Grid\Ncol;
use Inowas\Common\Grid\Nrow;
use Inowas\Common\Id\CalculationId;
use Inowas\Common\Modflow\Extension;
use Inowas\Common\Modflow\LayerValues;
use Inowas\Common\Modflow\TotalTimes;
use Inowas\ModflowBundle\Exception\NotFoundException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;

/** @noinspection LongInheritanceChainInspection */
class ModflowCalculationController extends InowasRestController
{

    /**
     * Get details of last calculation of modflow model by id.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get details of last calculation of modflow model by id.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @Rest\Get("/calculations/{id}")
     * @return JsonResponse
     * @throws NotFoundException
     */
    public function getCalculationDetailsAction(string $id): JsonResponse
    {
        $calculationId = CalculationId::fromString($id);
        $calculationDetails = $this->get('inowas.modflowmodel.calculation_results_finder')->getCalculationDetailsById($calculationId);

        if (! is_array($calculationDetails)) {
            throw NotFoundException::withMessage(sprintf('Calculation with id: \'%s\' not found.', $id));
        }

        return new JsonResponse($calculationDetails);
    }

    /**
     * Get calculation result times by calculationId.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the calculation result times of a calculation by id.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @Rest\Get("/calculations/{id}/results/times")
     * @return JsonResponse
     * @throws NotFoundException
     */
    public function getCalculationResultsTimesAction(string $id): JsonResponse
    {
        $totalTimes = $this->get('inowas.modflowmodel.calculation_results_finder')->getTotalTimesFromCalculationById(CalculationId::fromString($id));
        if (! $totalTimes instanceof TotalTimes) {
            throw NotFoundException::withMessage(sprintf('Calculation with id: \'%s\' not found.', $id));
        }

        return new JsonResponse($totalTimes);
    }

    /**
     * Get calculation layerValues by calculationId.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the calculation layerValues of a calculation by id.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @Rest\Get("/calculations/{id}/results/layervalues")
     * @return JsonResponse
     * @throws NotFoundException
     */
    public function getCalculationResultsLayerValuesAction(string $id): JsonResponse
    {
        $layerValues = $this->get('inowas.modflowmodel.calculation_results_finder')->findLayerValues(CalculationId::fromString($id));

        if (! $layerValues instanceof LayerValues) {
            throw NotFoundException::withMessage(sprintf('Calculation with id: \'%s\' not found.', $id));
        }

        return new JsonResponse($layerValues);
    }

    /**
     * Get file of modflow calculation
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the file of modflow calculation.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Get("/calculations/{id}/file/{extension}")
     * @param string $id
     * @param string $extension
     * @return JsonResponse
     */
    public function getCalculationFileAction(string $id, string $extension): JsonResponse
    {
        $file = $this->get('inowas.modflowmodel.calculation_results_finder')->getFile(CalculationId::fromString($id), Extension::fromString($extension));
        return new JsonResponse($file);
    }

    /**
     * Get fileList of modflow calculation
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the fileList of modflow calculation.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Get("/calculations/{id}/filelist")
     * @param string $id
     * @return JsonResponse
     */
    public function getCalculationFileListAction(string $id): JsonResponse
    {
        $list = $this->get('inowas.modflowmodel.calculation_results_finder')->getFileList(CalculationId::fromString($id));
        return new JsonResponse($list);
    }

    /** @noinspection MoreThanThreeArgumentsInspection
     * Get calculation headValues by calculationId.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the calculation headValues of a calculation by id, resultType, layerNumber, totalTime.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @param string $type
     * @param string $layer
     * @param string $totim
     * @Rest\Get("/calculations/{id}/results/types/{type}/layers/{layer}/totims/{totim}")
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\NotFoundException
     */
    public function getCalculationHeadResultsByTypeLayerAndTotimAction(string $id, string $type, string $layer, string $totim): JsonResponse
    {
        /** @var ResultType $type */
        $type = ResultType::fromString($type);
        $layerNumber = LayerNumber::fromInt((int)$layer);
        $totim = TotalTime::fromInt((int)$totim);

        $headData = $this->get('inowas.modflowmodel.calculation_results_finder')->findHeadData(
            CalculationId::fromString($id),
            $type,
            $layerNumber,
            $totim
        );

        if (! $headData instanceof HeadData) {
            throw NotFoundException::withMessage('HeadData not found.');
        }

        return new JsonResponse($headData);
    }

    /**
     * Get calculation headValues difference by calculationIds.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the calculation headValues difference of two calculations by ids, resultType, layerNumber, totalTime.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @param string $id2
     * @param string $type
     * @param string $layer
     * @param string $totim
     * @Rest\Get("/calculations/{id}/results/differences/{id2}/types/{type}/layers/{layer}/totims/{totim}")
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\NotFoundException
     */
    public function getCalculationHeadResultsDifferenceByTypeLayerAndTotimAction(string $id, string $id2, string $type, string $layer, string $totim): JsonResponse
    {

        $calculationId = CalculationId::fromString($id);
        $calculationId2 = CalculationId::fromString($id2);

        $type = ResultType::fromString($type);
        $layerNumber = LayerNumber::fromInt((int)$layer);
        $totim = TotalTime::fromInt((int)$totim);

        $headData = $this->get('inowas.modflowmodel.calculation_results_finder')->findHeadDifference(
            $calculationId,
            $calculationId2,
            $type,
            $layerNumber,
            $totim
        );

        if (! $headData instanceof HeadData) {
            throw NotFoundException::withMessage('HeadData not found.');
        }

        return new JsonResponse($headData);
    }

    /**
     * Get calculation timeseries by calculationId.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the calculation headValues of a calculation by id, resultType, layerNumber, totalTime.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @param string $type
     * @param string $layer
     * @param string $x
     * @param string $y
     * @Rest\Get("/calculations/{id}/results/timeseries/types/{type}/layers/{layer}/x/{x}/y/{y}")
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\NotFoundException
     */
    public function getCalculationTimeseriesByTypeLayerXAndYAction(string $id, string $type, string $layer, string $x, string $y): JsonResponse
    {
        $calculationId = CalculationId::fromString($id);

        $type = ResultType::fromString($type);
        $layerNumber = LayerNumber::fromInt((int)$layer);
        $nCol = Ncol::fromInt((int)$x);
        $nRow = Nrow::fromInt((int)$y);

        $timeSeriesData = $this->get('inowas.modflowmodel.calculation_results_finder')->findTimeSeries(
            $calculationId,
            $type,
            $layerNumber,
            $nRow,
            $nCol
        );

        if (! $timeSeriesData instanceof TimeSeriesData) {
            throw NotFoundException::withMessage('HeadData not found.');
        }

        return new JsonResponse($timeSeriesData);
    }
}
