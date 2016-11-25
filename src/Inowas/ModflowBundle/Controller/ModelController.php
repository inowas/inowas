<?php

namespace Inowas\ModflowBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Inowas\ModflowBundle\Model\GridSize;
use Inowas\ModflowBundle\Model\ModflowModel;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ModelController extends FOSRestController
{
    /**
     * Create a new Model.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Create a new Model.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Get("/models")
     * @return View
     */
    public function getModflowModelsAction()
    {
        $modelManager = $this->get('inowas.modflow.modelmanager');

        $view = View::create($modelManager->findAll())
            ->setStatusCode(200)
            ->setSerializationContext(SerializationContext::create()
                ->setGroups(array('list'))
            )
        ;

        return $view;
    }

    /**
     * Create a new Model.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Create a new Model.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Post("/models")
     * @param ParamFetcher $paramFetcher
     * @RequestParam(name="name", nullable=false, strict=true, description="Name of the new ModflowModel.")
     *
     * @return View
     */
    public function postModflowModelAction(ParamFetcher $paramFetcher)
    {
        $modelManager = $this->get('inowas.modflow.modelmanager');
        $model = $modelManager->create();
        $model->setName($paramFetcher->get('name'));
        $modelManager->update($model);

        $view = View::create($model)
            ->setStatusCode(200)
            ->setSerializationContext(SerializationContext::create()
                ->setGroups(array('details'))
            )
        ;

        return $view;
    }

    /**
     * Returns the model details specified by modelId.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the model details specified by modelId.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the model is not found"
     *   }
     * )
     *
     * @Rest\Get("/models/{id}")
     * @param $id
     * @return View
     * @throws NotFoundHttpException
     */
    public function getModflowModelAction($id)
    {
        $modelManager = $this->get('inowas.modflow.modelmanager');
        $model = $modelManager->findById($id);

        if (! $model instanceof ModflowModel){
            throw $this->createNotFoundException(sprintf('Model with id=%s not found.', $id));
        }

        $view = View::create($model)
            ->setStatusCode(200)
            ->setSerializationContext(SerializationContext::create()
                ->setGroups(array('details'))
            )
        ;

        return $view;
    }

    /**
     * @Rest\Put("/models/{id}")
     *
     * Update the model details specified by modelId.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the model details specified by modelId.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the model is not found"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     * @param $id
     *
     * @RequestParam(name="name", nullable=false, strict=false, description="Model name")
     * @RequestParam(name="description", nullable=false, strict=false, description="Model description")
     * @RequestParam(name="start", nullable=false, strict=false, description="Start date")
     * @RequestParam(name="end", nullable=false, strict=false, description="End date")
     * @RequestParam(name="gridsizeNx", nullable=false, strict=false, description="Gridsize, number of columns")
     * @RequestParam(name="gridsizeNy", nullable=false, strict=false, description="Gridsize, number of rows")
     * @RequestParam(name="soilmodelId", nullable=false, strict=false, description="Soilmodel Id")
     *
     * @return View
     * @throws NotFoundHttpException
     */
    public function putModflowModelAction(ParamFetcher $paramFetcher, $id)
    {
        $model = $this->get('inowas.modflow.modelmanager')->findById($id);

        if (! $model instanceof ModflowModel){
            throw $this->createNotFoundException(sprintf('Model with id=%s not found.', $id));
        }

        if ($paramFetcher->get('name')){
            $model->setName($paramFetcher->get('name'));
        }

        if ($paramFetcher->get('description')){
            $model->setDescription($paramFetcher->get('description'));
        }

        if ($paramFetcher->get('start')){
            $model->setStart(new \DateTime($paramFetcher->get('start')));
        }

        if ($paramFetcher->get('end')){
            $model->setEnd(new \DateTime($paramFetcher->get('end')));
        }

        if ($paramFetcher->get('gridsizeNx') && $paramFetcher->get('gridsizeNy')){
            $model->setGridSize(new GridSize($paramFetcher->get('gridsizeNx'), $paramFetcher->get('gridsizeNy')));
        }

        if ($paramFetcher->get('soilmodelId')){
            $model->setSoilmodelId($paramFetcher->get('soilmodelId'));
        }

        $this->get('inowas.modflow.modelmanager')->update($model);

        $view = View::create($model)
            ->setStatusCode(200)
            ->setSerializationContext(SerializationContext::create()
                ->setGroups(array('details'))
            )
        ;

        return $view;
    }
}
