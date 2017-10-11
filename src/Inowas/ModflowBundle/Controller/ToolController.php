<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Request\ParamFetcher;
use Inowas\ModflowBundle\Exception\InvalidArgumentException;
use Inowas\Tool\Model\ToolId;
use Inowas\Tool\Model\ToolType;
use Nelmio\ApiDocBundle\Annotation\ApiDoc as ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     * @Rest\QueryParam(name="public", default=false, description="List all public toolInstances.")
     * @param string $type
     * @param ParamFetcher $paramFetcher
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\InvalidArgumentException
     */
    public function getToolsByTypeAction(string $type, ParamFetcher $paramFetcher): JsonResponse
    {
        $userId = $this->getUserId();
        $toolType = ToolType::fromString($type);
        $showPrivateTools = !$paramFetcher->get('public');

        if (! ToolType::isValid($type)) {
            throw InvalidArgumentException::withMessage(sprintf('The ToolType %s is not valid. Available types are: %s', $type, implode(', ', ToolType::$availableTypes)));
        }

        if ($showPrivateTools) {
            $result = $this->get('inowas.tool.tools_finder')->findByUserIdAndType($userId, $toolType);
            return new JsonResponse($result);
        }

        $result = $this->get('inowas.tool.tools_finder')->findPublicByType($toolType);
        return new JsonResponse($result);
    }

    /**
     * Get details of tool instance by toolType and id.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get details of tool instance by toolType and id.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Get("/tools/{type}/{id}")
     * @param string $type
     * @param string $id
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\InvalidArgumentException
     */
    public function getToolDetailsAction(string $type, string $id): JsonResponse
    {
        $userId = $this->getUserId();
        $toolType = ToolType::fromString($type);
        $toolId = ToolId::fromString($id);

        if (! ToolType::isValid($type)) {
            throw InvalidArgumentException::withMessage(sprintf('The ToolType %s is not valid. Available types are: %s', $type, implode(', ', ToolType::$availableTypes)));
        }

        $result = $this->get('inowas.tool.tools_finder')->findByUserIdTypeAndId($userId, $toolType, $toolId);

        return new JsonResponse($result);
    }

}
