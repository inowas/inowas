<?php

namespace Inowas\ScenarioAnalysisBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use HeatMap\HeatMap;
use Inowas\ModflowBundle\Model\Head;
use Inowas\ModflowBundle\Service\HeadsManager;
use Inowas\ScenarioAnalysisBundle\Model\Scenario;
use Inowas\ScenarioAnalysisBundle\Service\ScenarioManager;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ScenarioHeadsImageController extends FOSRestController
{
    /**
     * Get head values
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Post head values.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the Calculation-Id is not found"
     *   }
     * )
     *
     * @Rest\Route("/models/{id}/heads", methods={"GET"}, requirements={"_format"="png"})
     *
     * @param ParamFetcher $paramFetcher
     * @param $id
     * @return Response
     *
     * @Rest\QueryParam(name="totim", requirements="\d+", default=364, description="Time in days from beginning")
     * @Rest\QueryParam(name="layer", requirements="\d+", default=0, description="Layer number")
     * @Rest\QueryParam(name="max", default=null, description="Value of the spectrum maximum")
     * @Rest\QueryParam(name="min", default=null, description="Value of the spectrum minimum")
     * @Rest\QueryParam(name="upper", requirements="\d+", default=95, description="Percentile spectrum max")
     * @Rest\QueryParam(name="loper", requirements="\d+", default=5, description="Percentile spectrum min")
     */
    public function getScenarioHeadsImageAction(ParamFetcher $paramFetcher, $id){

        /** @var ScenarioManager $scenarioManager */
        $scenarioManager = $this->get('inowas.scenarioanalysis.scenariomanager');
        $scenario = $scenarioManager->findById($id);

        if (! $scenario instanceof Scenario){
            throw new NotFoundHttpException(sprintf('Scenarios with Id=%s not found.', $id));
        }

        /** @var HeadsManager $headsManager */
        $headsManager = $this->get('inowas.modflow.headsmanager');
        $head = $headsManager->getHead($scenario, $paramFetcher->get('totim'), $paramFetcher->get('layer'));


        if (! $head instanceof Head){
            throw new NotFoundHttpException(
                sprintf('Head values for totim = %s and layer %s not found.', $paramFetcher->get('totim'), $paramFetcher->get('layer')));
        }

        $data = $head->getData();
        $heatMap = new HeatMap();

        if ($paramFetcher->get('min') && $paramFetcher->get('max')){
            $file = $heatMap->createWithAbsoluteLimits($data, $paramFetcher->get('min'), $paramFetcher->get('max'));
        } else {
            $file = $heatMap->createWithPercentileLimits($data, $paramFetcher->get('loper'), $paramFetcher->get('upper'));
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'image/png');
        $response->setContent(file_get_contents($file));
        return $response;
    }
}
