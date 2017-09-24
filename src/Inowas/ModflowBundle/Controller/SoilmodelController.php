<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Inowas\Common\Soilmodel\SoilmodelId;
use Inowas\ModflowBundle\Exception\NotFoundException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\JsonResponse;

/** @noinspection LongInheritanceChainInspection */
class SoilmodelController extends InowasRestController
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
     * @Rest\Get("/soilmodels/{id}")
     * @return JsonResponse
     * @throws NotFoundException
     */
    public function getSoilmodelAction(string $id): JsonResponse
    {
        $this->assertUuidIsValid($id);
        $soilmodelId = SoilmodelId::fromString($id);

        $soilmodelDetails = $this->get('inowas.soilmodel.soil_model_finder')->getDetailsBySoilmodelId($soilmodelId);

        if (! is_array($soilmodelDetails)) {
            throw NotFoundException::withMessage(sprintf('Soilmodel with id: \'%s\' not found.', $id));
        }

        return new JsonResponse($soilmodelDetails);
    }
}
