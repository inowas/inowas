<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Inowas\Common\Id\ModflowId;
use Inowas\ModflowBundle\Exception\InvalidArgumentException;
use Inowas\ModflowBundle\Exception\InvalidUuidException;
use Inowas\ModflowBundle\Exception\NotFoundException;
use Inowas\ScenarioAnalysis\Model\Command\CloneScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenario;
use Inowas\ScenarioAnalysis\Model\Command\DeleteScenario;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Nelmio\ApiDocBundle\Annotation\ApiDoc as ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/** @noinspection LongInheritanceChainInspection */
class ScenarioAnalysisController extends InowasRestController
{

    /**
     * Get list of my scenarioAnalysis-projects.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get list of my scenarioAnalysis-projects.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Get("/scenarioanalyses")
     * @return JsonResponse
     */
    public function getMyScenarioAnalysesAction(): JsonResponse
    {
        $userId = $this->getUserId();
        $result = $this->get('inowas.scenarioanalysis.scenarioanalysis_finder')->findScenarioAnalysesByUserId($userId);
        return new JsonResponse($result);
    }

    /**
     * Get list of all public scenarioAnalysis-projects.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get list of all public scenarioAnalysis-projects.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Get("/scenarioanalyses/public")
     * @return JsonResponse
     */
    public function getPublicScenarioAnalysesAction(): JsonResponse
    {
        $result = $this->get('inowas.scenarioanalysis.scenarioanalysis_finder')->findPublicScenarioAnalyses();
        return new JsonResponse($result);
    }

    /**
     * Get ScenarioAnalysis details by ScenarioAnalysisId.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get ScenarioAnalysis details by ScenarioAnalysisId.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Get("/scenarioanalyses/{id}")
     * @param $id
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\NotFoundException
     * @throws InvalidUuidException
     * @throws InvalidArgumentException
     */
    public function getScenariosAnalysisDetailsAction(string $id): JsonResponse
    {
        $this->assertUuidIsValid($id);
        $scenarioAnalysisId = ScenarioAnalysisId::fromString($id);
        $scenarioAnalysis = $this->get('inowas.scenarioanalysis.scenarioanalysis_finder')->findScenarioAnalysisDetailsById($scenarioAnalysisId);

        if (null === $scenarioAnalysis){
            throw NotFoundException::withMessage(sprintf('ScenarioAnalysis with id %s was not found.', $scenarioAnalysisId->toString()));
        }

        $userId = $this->getUserId();
        $permissions = $this->get('inowas.user_permissions')->getModelPermissions($userId, ModflowId::fromString($scenarioAnalysis['base_model']['id']))->toString();

        if (!empty($scenarioAnalysis['base_model'])) {
            $scenarioAnalysis['base_model']['permissions'] = $permissions;
        }

        $scenarioAnalysis['permissions'] = $permissions;

        return new JsonResponse($scenarioAnalysis);
    }

    /**
     * Clone ScenarioAnalysis by ScenarioAnalysisId.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Clone ScenarioAnalysis by ScenarioAnalysisId.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Post("/scenarioanalyses/{id}/clone")
     * @param $id
     * @return RedirectResponse
     * @throws \InvalidArgumentException
     * @throws \Prooph\ServiceBus\Exception\CommandDispatchException
     * @throws InvalidUuidException
     * @throws InvalidArgumentException
     */
    public function postScenariosAnalysisCloneAction(string $id): RedirectResponse
    {
        $userId = $this->getUserId();
        $this->assertUuidIsValid($id);
        $scenarioAnalysisId = ScenarioAnalysisId::fromString($id);
        $newScenarioAnalysisId = ScenarioAnalysisId::generate();

        $this->get('prooph_service_bus.modflow_command_bus')->dispatch(CloneScenarioAnalysis::byUserWithIds($userId, $scenarioAnalysisId, $newScenarioAnalysisId));

        return new RedirectResponse(
            $this->generateUrl('get_my_tools'),
            302
        );
    }

    /**
     * Clone Scenario by ScenarioAnalysisId and ScenarioId.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Clone Scenario by ScenarioAnalysisId and ScenarioId.",
     *   statusCodes = {
     *     302 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Post("/scenarioanalyses/{id}/scenarios/{sid}/clone")
     * @param string $id
     * @param string $sid
     * @return RedirectResponse
     * @throws \InvalidArgumentException
     * @throws \Prooph\ServiceBus\Exception\CommandDispatchException
     */
    public function postCloneScenarioAction(string $id, string $sid): RedirectResponse
    {
        $userId = $this->getUserId();
        $this->assertUuidIsValid($id);
        $scenarioAnalysisId = ScenarioAnalysisId::fromString($id);

        $this->assertUuidIsValid($sid);
        $baseScenarioId = ModflowId::fromString($sid);
        $newScenarioId = ModflowId::generate();

        $this->get('prooph_service_bus.modflow_command_bus')->dispatch(
            CreateScenario::byUserWithIds(
                $scenarioAnalysisId,
                $userId,
                $baseScenarioId,
                $newScenarioId
            )
        );

        return new RedirectResponse(
            $this->generateUrl('get_scenarios_analysis_details', array( 'id' => $id )),
            302
        );
    }

    /**
     * Clone Scenario by ScenarioAnalysisId and ScenarioId.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Clone Scenario by ScenarioAnalysisId and ScenarioId.",
     *   statusCodes = {
     *     302 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Delete("/scenarioanalyses/{id}/scenarios/{sid}")
     * @param string $id
     * @param string $sid
     * @return RedirectResponse
     * @throws \InvalidArgumentException
     * @throws \Prooph\ServiceBus\Exception\CommandDispatchException
     */
    public function deleteScenarioAction(string $id, string $sid): RedirectResponse
    {
        $userId = $this->getUserId();
        $this->assertUuidIsValid($id);
        $scenarioAnalysisId = ScenarioAnalysisId::fromString($id);

        $this->assertUuidIsValid($sid);
        $scenarioId = ModflowId::fromString($sid);

        $this->get('prooph_service_bus.modflow_command_bus')->dispatch(DeleteScenario::byUserWithIds($scenarioAnalysisId, $userId, $scenarioId));

        return new RedirectResponse(
            $this->generateUrl('get_scenarios_analysis_details', array( 'id' => $id )),
            303
        );
    }
}
