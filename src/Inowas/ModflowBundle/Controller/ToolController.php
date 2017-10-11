<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Inowas\ModflowBundle\Exception\AccessDeniedException;
use Inowas\ModflowBundle\Exception\InvalidArgumentException;
use Inowas\ModflowBundle\Exception\NotFoundException;
use Inowas\ScenarioAnalysis\Model\Command\CloneScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\Command\DeleteScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\ScenarioAnalysisId;
use Inowas\Tool\Model\ToolId;
use Inowas\Tool\Model\ToolType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc as ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;

/** @noinspection LongInheritanceChainInspection */
class ToolController extends InowasRestController
{

    /**
     * Get list of my tool instances.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get list of my tool instances.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Get("/tools")
     * @return JsonResponse
     */
    public function getMyToolsAction(): JsonResponse
    {
        $userId = $this->getUserId();
        $result = $this->get('inowas.tool.tools_finder')->findByUserId($userId);
        return new JsonResponse($result);
    }

    /**
     * Get list of my tool instances by toolType.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get list of my tool instances by toolType.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Get("/tools/{type}")
     * @param string $type
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\InvalidArgumentException
     */
    public function getMyToolsByTypeAction(string $type): JsonResponse
    {
        $userId = $this->getUserId();

        if (! ToolType::isValid($type)) {
            throw InvalidArgumentException::withMessage(sprintf('The ToolType %s is not valid. Available types are: %s', $type, implode(', ', ToolType::$availableTypes)));
        }

        $toolType = ToolType::fromString($type);
        $result = $this->get('inowas.tool.tools_finder')->findByUserIdAndType($userId, $toolType);

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
     * @Rest\Get("/tools/{type}/public")
     * @param string $type
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\InvalidArgumentException
     */
    public function getPublicToolsAction(string $type): JsonResponse
    {
        $this->getUserId();
        $toolType = ToolType::fromString($type);

        if (! ToolType::isValid($type)) {
            throw InvalidArgumentException::withMessage(sprintf('The ToolType %s is not valid. Available types are: %s', $type, implode(', ', ToolType::$availableTypes)));
        }

        $result = $this->get('inowas.tool.tools_finder')->findPublicByType($toolType);
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
     * @Rest\Post("/tools/{id}/clone")
     * @param string $id
     * @return RedirectResponse
     * @throws \Inowas\ModflowBundle\Exception\AccessDeniedException
     * @throws \Inowas\ModflowBundle\Exception\InvalidArgumentException
     * @throws \Prooph\ServiceBus\Exception\CommandDispatchException
     * @throws \InvalidArgumentException
     * @throws NotFoundException
     */
    public function cloneToolAction(string $id): RedirectResponse
    {
        $userId = $this->getUserId();

        $this->assertUuidIsValid($id);
        $projectId = ToolId::fromString($id);

        $projectsFinder = $this->get('inowas.tool.tools_finder');

        /** @var ToolType $toolType */
        $toolType = $projectsFinder->getToolTypeById($projectId);

        if (!$toolType instanceof ToolType) {
            throw InvalidArgumentException::withMessage(sprintf(
                'The project with id %s was not found', $projectId->toString()
            ));
        }

        if (! $projectsFinder->canBeClonedByUser($projectId, $userId)){
            throw AccessDeniedException::withMessage(sprintf(
                'Access denied to clone Project with id %s.',
                $projectId->toString()
            ));
        }

        switch ($toolType->toString()) {
            case ToolType::SCENARIOANALYSIS:
                $this->get('prooph_service_bus.modflow_command_bus')->dispatch(CloneScenarioAnalysis::byUserWithIds(
                    $userId,
                    ScenarioAnalysisId::fromString($projectId->toString()),
                    ScenarioAnalysisId::generate()
                ));
                break;
        }

        return new RedirectResponse(
            $this->generateUrl('get_my_tools'),
            302
        );
    }

    /**
     * Delete tool by id and user.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Delete tool by id and user.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Delete("/tools/{id}")
     * @param string $id
     * @return RedirectResponse
     * @throws \Inowas\ModflowBundle\Exception\AccessDeniedException
     * @throws \Inowas\ModflowBundle\Exception\InvalidArgumentException
     * @throws \Prooph\ServiceBus\Exception\CommandDispatchException
     * @throws \InvalidArgumentException
     * @throws NotFoundException
     */
    public function deleteToolByIdAction(string $id): RedirectResponse
    {
        $userId = $this->getUserId();

        $this->assertUuidIsValid($id);
        $toolId = ToolId::fromString($id);

        $toolFinder = $this->get('inowas.tool.tools_finder');

        /** @var ToolType $toolType */
        $toolType = $toolFinder->getToolTypeById($toolId);

        if (!$toolType instanceof ToolType) {
            throw InvalidArgumentException::withMessage(sprintf(
                'The project with id %s was not found', $toolId->toString()
            ));
        }

        switch ($toolType->toString()) {
            case ToolType::SCENARIOANALYSIS:
                $this->get('prooph_service_bus.modflow_command_bus')->dispatch(
                    DeleteScenarioAnalysis::byUserWithId(
                        $userId,
                        ScenarioAnalysisId::fromString($toolId->toString())
                    )
                );
                break;
        }

        return new RedirectResponse(
            $this->generateUrl('get_my_tools'),
            303
        );
    }
}
