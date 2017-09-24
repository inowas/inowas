<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use HeatMap\HeatMap;
use Inowas\Common\Calculation\HeadData;
use Inowas\Common\Calculation\ResultType;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\CalculationId;
use Inowas\Common\DateTime\TotalTime;
use Inowas\ModflowBundle\Exception\InvalidUuidException;
use Inowas\ModflowBundle\Exception\NotFoundException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc as ApiDoc;
use Symfony\Component\HttpFoundation\Response;

class ModflowCalculationImageController extends InowasRestController
{
    /**
     * Get calculation result as image by calculationId, type, layerNumber and totim.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get calculation result as image by calculationId, type, layerNumber and totim.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the Calculation-Id is not found"
     *   }
     * )
     *
     * @Rest\Route("/calculations/{id}/results/types/{type}/layers/{layer}/totims/{totim}", methods={"GET"}, requirements={"_format"="png"})
     *
     * @param ParamFetcher $paramFetcher
     * @param $id
     * @param $type
     * @param $layer
     * @param $totim
     * @return Response
     * @throws \Inowas\ModflowBundle\Exception\NotFoundException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws InvalidUuidException
     *
     * @Rest\QueryParam(name="max", default=null, description="Value of the spectrum maximum")
     * @Rest\QueryParam(name="min", default=null, description="Value of the spectrum minimum")
     * @Rest\QueryParam(name="upper", requirements="\d+", default=95, description="Percentile spectrum max")
     * @Rest\QueryParam(name="loper", requirements="\d+", default=5, description="Percentile spectrum min")
     */
    public function getCalculationImageHeadResultsByTypeLayerAndTotimAction(ParamFetcher $paramFetcher, string $id, string $type, string $layer, string $totim): Response
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

        #$spectrum = array('purple', 'red', 'yellow', 'lime', 'aqua', 'blue');
        $spectrum = array('#800080', '#FF0000', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF');
        $heatMap = new HeatMap($spectrum);
        if ($paramFetcher->get('min') && $paramFetcher->get('max')){
            $file = $heatMap->createWithAbsoluteLimits($headData->toArray(), (float)$paramFetcher->get('min'), (float)$paramFetcher->get('max'));
        } else {
            $file = $heatMap->createWithPercentileLimits($headData->toArray(), (float)$paramFetcher->get('loper'), (float)$paramFetcher->get('upper'));
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'image/png');
        $response->setContent(file_get_contents($file));
        return $response;
    }


    /**
     * Get calculation headValues difference image by calculationIds, type, layernumber, totim.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get calculation headValues difference image by calculationIds, type, layernumber, totim.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the Calculation-Id is not found"
     *   }
     * )
     *
     * @Rest\Route("/calculations/{id}/results/differences/{id2}/types/{type}/layers/{layer}/totims/{totim}", methods={"GET"}, requirements={"_format"="png"})
     *
     * @param ParamFetcher $paramFetcher
     * @param $id
     * @param $id2
     * @param $type
     * @param $layer
     * @param $totim
     * @return Response
     * @throws \Inowas\ModflowBundle\Exception\NotFoundException
     * @throws \UnexpectedValueException
     * @throws \InvalidArgumentException
     * @throws InvalidUuidException
     *
     * @Rest\QueryParam(name="max", default=null, description="Value of the spectrum maximum")
     * @Rest\QueryParam(name="min", default=null, description="Value of the spectrum minimum")
     * @Rest\QueryParam(name="upper", requirements="\d+", default=95, description="Percentile spectrum max")
     * @Rest\QueryParam(name="loper", requirements="\d+", default=5, description="Percentile spectrum min")
     */
    public function getCalculationImageHeadResultsDifferenceByTypeLayerAndTotimAction(ParamFetcher $paramFetcher, $id, $id2, $type, $layer, $totim): Response
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

        #$spectrum = array('purple', 'red', 'yellow', 'lime', 'aqua', 'blue');
        $spectrum = array('#800080', '#FF0000', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF');
        $heatMap = new HeatMap($spectrum);
        if ($paramFetcher->get('min') && $paramFetcher->get('max')){
            $file = $heatMap->createWithAbsoluteLimits($headData->toArray(), (float)$paramFetcher->get('min'), (float)$paramFetcher->get('max'));
        } else {
            $file = $heatMap->createWithPercentileLimits($headData->toArray(), (float)$paramFetcher->get('loper'), (float)$paramFetcher->get('upper'));
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'image/png');
        $response->setContent(file_get_contents($file));
        return $response;
    }
}
