<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Inowas\Common\Calculation\CalculationState;
use Inowas\Common\Calculation\CalculationStateQuery;
use Inowas\Common\Id\BoundaryId;
use Inowas\Common\Id\CalculationId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Modflow\ModflowModel;
use Inowas\Common\Modflow\PackageName;
use Inowas\Common\Modflow\Results;
use Inowas\Common\Soilmodel\Layer;
use Inowas\Common\Soilmodel\LayerId;
use Inowas\Common\Soilmodel\SoilmodelQuery;
use Inowas\ModflowBundle\Exception\AccessDeniedException;
use Inowas\ModflowBundle\Exception\NotFoundException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;

/** @noinspection LongInheritanceChainInspection */
class ModflowModelController extends InowasRestController
{
    # MODEL-LISTING
    /**
     * Get list of modflow models from user
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get list of modflow models from user.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Get("/modflowmodels")
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\UserNotAuthenticatedException
     */
    public function getModflowModelsAction(): JsonResponse
    {
        return new JsonResponse(
            $this->get('inowas.modflowmodel.model_finder')->findModelsByBaseUserId($this->getUserId())
        );
    }

    /**
     * Get list of all public modflow models
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get list of my Modflow Models.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Get("/modflowmodels/public")
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\UserNotAuthenticatedException
     */
    public function getPublicModflowModelsAction(): JsonResponse
    {
        $this->assertUserIsLoggedInCorrectly();

        return new JsonResponse(
            $this->get('inowas.modflowmodel.model_finder')->findPublicModels()
        );
    }

    /**
     * Get details of modflow model by id.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get details of modflow model by id.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @Rest\Get("/modflowmodels/{id}")
     * @return JsonResponse
     * @throws \InvalidArgumentException
     * @throws \Inowas\ModflowBundle\Exception\AccessDeniedException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws NotFoundException
     * @throws \Inowas\ModflowBundle\Exception\UserNotAuthenticatedException
     */
    public function getModflowModelAction(string $id): JsonResponse
    {
        $this->assertUuidIsValid($id);
        $modelId = ModflowId::fromString($id);
        $userId = $this->getUserId();

        if (! $this->get('inowas.modflowmodel.model_finder')->userHasReadAccessToModel($userId, $modelId)) {
            throw AccessDeniedException::withMessage(
                sprintf(
                    'Model not found or user with Id %s does not have access to model with id %s',
                    $userId->toString(),
                    $modelId->toString()
                )
            );
        }

        $model = $this->container->get('inowas.modflowmodel.manager')->findModel($modelId, $userId);

        if (! $model instanceof ModflowModel) {
            throw NotFoundException::withMessage(sprintf(
                'ModflowModel with id: \'%s\' not found.', $modelId->toString()
            ));
        }

        return (new JsonResponse())->setData($model->toArray());
    }

    /**
     * Get activeCells of modflow model by id.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get details of modflow model by id.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @Rest\Get("/modflowmodels/{id}/activecells")
     * @return JsonResponse
     * @throws \InvalidArgumentException
     * @throws \Inowas\ModflowBundle\Exception\AccessDeniedException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws NotFoundException
     * @throws \Inowas\ModflowBundle\Exception\UserNotAuthenticatedException
     */
    public function getModflowModelActiveCellsAction(string $id): JsonResponse
    {
        $this->assertUuidIsValid($id);
        $modelId = ModflowId::fromString($id);
        $userId = $this->getUserId();

        if (! $this->get('inowas.modflowmodel.model_finder')->userHasReadAccessToModel($userId, $modelId)) {
            throw AccessDeniedException::withMessage(
                sprintf(
                    'Model not found or user with Id %s does not have access to model with id %s',
                    $userId->toString(),
                    $modelId->toString()
                )
            );
        }

        $activeCells = $this->container->get('inowas.modflowmodel.manager')->getAreaActiveCells($modelId);

        return (new JsonResponse())->setData($activeCells->cells2D());
    }

    /**
     * Get list of boundaries from modflowmodel by id.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns a list of all boundaries with ids, types, names, geometry.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @Rest\Get("/modflowmodels/{id}/boundaries")
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\NotFoundException
     * @throws \Inowas\ModflowBundle\Exception\UserNotAuthenticatedException
     */
    public function getModflowModelBoundaryListAction(string $id): JsonResponse
    {
        $this->assertUserIsLoggedInCorrectly();
        $this->assertUuidIsValid($id);
        $modelId = ModflowId::fromString($id);

        $boundaryList = $this->get('inowas.modflowmodel.boundary_manager')->getBoundaryList($modelId);

        if (null === $boundaryList) {
            throw NotFoundException::withMessage(sprintf(
                'ModflowModel with id: \'%s\' not found.', $modelId->toString()
            ));
        }

        return new JsonResponse($boundaryList->toArray());
    }

    /**
     * Get boundary by modelId and boundaryId.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the boundary.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @param string $bid
     * @Rest\Get("/modflowmodels/{id}/boundaries/{bid}")
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\NotFoundException
     */
    public function getModflowModelBoundaryAction(string $id, string $bid): JsonResponse
    {
        $this->assertUuidIsValid($id);
        $modelId = ModflowId::fromString($id);
        $boundaryId = BoundaryId::fromString($bid);

        $boundary = $this->get('inowas.modflowmodel.boundary_manager')->getBoundary($modelId, $boundaryId);

        if (null === $boundary){
            throw NotFoundException::withMessage(sprintf(
                'ModflowModel with id: \'%s\' and BoundaryId \'%s\' not found.', $modelId->toString(), $boundaryId->toString()
            ));
        }

        if ($boundary->affectedCells()->isEmpty()) {
            $affectedCells = $this->get('inowas.modflowmodel.active_cells_manager')->getBoundaryActiveCells($modelId, $boundaryId)->affectedCells();
            $boundary = $boundary->updateAffectedCells($affectedCells);
        }

        return new JsonResponse($boundary->toArray());
    }

    /**
     * Get packages modflowModel by id.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get packages modflowModel by id.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @return JsonResponse
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws AccessDeniedException
     * @Rest\Get("/modflowmodels/{id}/packages")
     * @throws \Inowas\ModflowBundle\Exception\UserNotAuthenticatedException
     */
    public function getModflowModelPackagesAction(string $id): JsonResponse
    {
        $this->assertUuidIsValid($id);
        $modelId = ModflowId::fromString($id);
        $userId = $this->getUserId();

        if (! $this->get('inowas.modflowmodel.model_finder')->userHasReadAccessToModel($userId, $modelId)) {
            throw AccessDeniedException::withMessage(
                sprintf(
                    'Model not found or user with Id %s does not have access to model with id %s',
                    $userId->toString(),
                    $modelId->toString()
                )
            );
        }

        $packages = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getPackagesByModelId($modelId);
        return new JsonResponse($packages->metaData());
    }

    /**
     * Get package details of modflowModel by id.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get package details of modflowModel by id.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @param string $package
     * @return JsonResponse
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws AccessDeniedException
     * @Rest\Get("/modflowmodels/{id}/packages/{package}")
     * @throws \Inowas\ModflowBundle\Exception\UserNotAuthenticatedException
     */
    public function getModflowModelPackageAction(string $id, string $package): JsonResponse
    {
        $this->assertUuidIsValid($id);
        $modelId = ModflowId::fromString($id);
        $userId = $this->getUserId();

        if (! $this->get('inowas.modflowmodel.model_finder')->userHasReadAccessToModel($userId, $modelId)) {
            throw AccessDeniedException::withMessage(
                sprintf(
                    'Model not found or user with Id %s does not have access to model with id %s',
                    $userId->toString(),
                    $modelId->toString()
                )
            );
        }

        $packages = $this->container->get('inowas.modflowmodel.modflow_packages_manager')->getPackagesByModelId($modelId);
        $packageName = PackageName::fromString($package);

        return new JsonResponse($packages->getPackage($packageName->toString())->getEditables());
    }

    /**
     * Get details of modflow model by id.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get details of modflow model by id.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @Rest\Get("/modflowmodels/{id}/stressperiods")
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\AccessDeniedException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws NotFoundException
     * @throws \Inowas\ModflowBundle\Exception\UserNotAuthenticatedException
     */
    public function getModflowModelStressPeriodsAction(string $id): JsonResponse
    {
        $this->assertUuidIsValid($id);
        $modelId = ModflowId::fromString($id);
        $userId = $this->getUserId();

        if (! $this->get('inowas.modflowmodel.model_finder')->userHasReadAccessToModel($userId, $modelId)) {
            throw AccessDeniedException::withMessage(
                sprintf(
                    'Model not found or user with Id %s does not have access to model with id %s',
                    $userId->toString(),
                    $modelId->toString()
                )
            );
        }

        $stressPeriods = $this->container->get('inowas.modflowmodel.manager')->getStressPeriodsByModelId($modelId);
        return new JsonResponse($stressPeriods);
    }

    /**
     * Get calculation results by model id.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get calculation results by model id.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @Rest\Get("/modflowmodels/{id}/results")
     * @return JsonResponse
     * @throws \InvalidArgumentException
     * @throws NotFoundException
     */
    public function getModflowModelResultsAction(string $id): JsonResponse
    {
        $this->assertUuidIsValid($id);
        $modelId = ModflowId::fromString($id);
        $calculationId = $this->get('inowas.modflowmodel.model_finder')->getCalculationIdByModelId($modelId);

        if (!$calculationId instanceof CalculationId) {
            return new JsonResponse([]);
        }

        $results = $this->get('inowas.modflowmodel.calculation_results_finder')->getCalculationResults($calculationId);

        if (!$results instanceof Results) {
            return new JsonResponse([]);
        }

        return new JsonResponse($results->toArray());
    }

    /* Soilmodel */
    /**
     * Get soilmodel of modflowModel by id.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get soilmodel details of modflowModel by id.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @Rest\Get("/modflowmodels/{id}/soilmodel")
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\NotFoundException
     * @throws \Inowas\ModflowBundle\Exception\AccessDeniedException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Prooph\ServiceBus\Exception\CommandDispatchException
     * @throws \InvalidArgumentException
     * @throws \Inowas\ModflowBundle\Exception\UserNotAuthenticatedException
     */
    public function getSoilModelAction(string $id): JsonResponse
    {
        $this->assertUuidIsValid($id);
        $modelId = ModflowId::fromString($id);

        $userId = $this->getUserId();

        if (! $this->get('inowas.modflowmodel.model_finder')->userHasReadAccessToModel($userId, $modelId)) {
            throw AccessDeniedException::withMessage(
                sprintf(
                    'Model not found or user with Id %s does not have access to model with id %s',
                    $userId->toString(),
                    $modelId->toString()
                )
            );
        }

        $soilmodelQuery = $this->container->get('inowas.modflowmodel.soilmodel_finder')->getSoilmodelQuery($modelId);

        if (!$soilmodelQuery instanceof SoilmodelQuery) {
            throw NotFoundException::withMessage(sprintf(
                'Soilmodel from Model with id: \'%s\' not found.', $modelId->toString()
            ));
        }

        return new JsonResponse($soilmodelQuery->toGeneralArray());
    }

    /**
     * Get layer details of soilmodel by modelId.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Get layer details of soilmodel by modelId.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @param string $lid
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\NotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws AccessDeniedException
     * @Rest\Get("/modflowmodels/{id}/soilmodel/{lid}")
     * @throws \Inowas\ModflowBundle\Exception\UserNotAuthenticatedException
     */
    public function getLayerAction(string $id, string $lid): JsonResponse
    {
        $this->assertUuidIsValid($id);
        $modelId = ModflowId::fromString($id);
        $layerId = LayerId::fromString($lid);

        $userId = $this->getUserId();

        if (! $this->get('inowas.modflowmodel.model_finder')->userHasReadAccessToModel($userId, $modelId)) {
            throw AccessDeniedException::withMessage(
                sprintf(
                    'Model not found or user with Id %s does not have access to model with id %s',
                    $userId->toString(),
                    $modelId->toString()
                )
            );
        }

        $layer = $this->container->get('inowas.modflowmodel.soilmodel_finder')->findLayer($modelId, $layerId);

        if (!$layer instanceof Layer) {
            throw NotFoundException::withMessage(sprintf(
                'Layer with Id %s from Model with id: %s not found.', $layerId->toString(), $modelId->toString()
            ));
        }

        return new JsonResponse($layer->toArray());
    }

    /**
     * Returns the calculation state of the model.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the calculation state of the model",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @param string $id
     * @Rest\Get("/modflowmodels/{id}/calculation")
     * @return JsonResponse
     * @throws \Inowas\ModflowBundle\Exception\AccessDeniedException
     * @throws \Inowas\ModflowBundle\Exception\UserNotAuthenticatedException
     */
    public function getModflowModelCalculationAction(string $id): JsonResponse
    {
        $this->assertUuidIsValid($id);
        $modelId = ModflowId::fromString($id);
        $userId = $this->getUserId();

        if (! $this->get('inowas.modflowmodel.model_finder')->userHasReadAccessToModel($userId, $modelId)) {
            throw AccessDeniedException::withMessage(
                sprintf(
                    'Model not found or user with Id %s does not have access to model with id %s',
                    $userId->toString(),
                    $modelId->toString()
                )
            );
        }

        $calculationId = $this->get('inowas.modflowmodel.model_finder')->getCalculationIdByModelId($modelId);
        if (!$calculationId instanceof CalculationId) {
            $query = CalculationStateQuery::createWithEmptyCalculationId(
                CalculationState::new()
            );

            return new JsonResponse($query);
        }

        $query = $this->get('inowas.modflowmodel.calculation_results_finder')->getCalculationStateQuery($calculationId);
        if ($query instanceof CalculationStateQuery) {

            if ($query->calculationWasFinished()) {
                $query->updateFiles($this->get('inowas.modflowmodel.calculation_results_finder')->getFileList($calculationId));
            }

            return new JsonResponse($query);
        }

        $query = CalculationStateQuery::createWithEmptyCalculationId(
            CalculationState::new()
        );

        return new JsonResponse($query);
    }
}
