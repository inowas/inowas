<?php

namespace Inowas\ModflowBundle\Controller;

use CrEOF\Spatial\PHP\Types\Geometry\LineString;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use CrEOF\Spatial\PHP\Types\Geometry\Polygon;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use Inowas\ModflowBundle\Exception\InvalidArgumentException;
use Inowas\ModflowBundle\Model\Boundary\Boundary;
use Inowas\ModflowBundle\Model\Boundary\ConstantHeadBoundary;
use Inowas\ModflowBundle\Model\Boundary\GeneralHeadBoundary;
use Inowas\ModflowBundle\Model\Boundary\RechargeBoundary;
use Inowas\ModflowBundle\Model\Boundary\RiverBoundary;
use Inowas\ModflowBundle\Model\Boundary\WellBoundary;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints\Uuid;

class BoundaryController extends FOSRestController
{
    /**
     * Returns the boundary details specified by boundary-ID.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns the boundary details by id.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the model is not found"
     *   }
     * )
     *
     * @Rest\Get("/boundaries/{id}")
     * @param $id
     * @return View
     */
    public function getBoundariesAction($id)
    {
        $manager = $this->get('inowas.modflow.boundarymanager');
        $boundary = $manager->findById($id);
        $boundary->getObservationPoints();

        if (! $boundary instanceof Boundary){
            throw $this->createNotFoundException(sprintf('Boundary with id=%s not found.', $id));
        }

        $view = View::create($boundary)
            ->setStatusCode(200)
            ->setSerializationContext(SerializationContext::create()
                ->setGroups(array('details'))
            )
        ;

        return $view;
    }

    /**
     * Updates the boundary details specified by boundary-ID.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Updates the boundary details specified by boundary-ID.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the model is not found"
     *   }
     * )
     *
     * @RequestParam(name="name", nullable=false, strict=false, description="Name of the Boundary.")
     * @RequestParam(name="active_cells", nullable=false, strict=false, description="Active Cells Json")
     * @RequestParam(name="geometry", nullable=false, strict=false, description="The Boundary geometry in geoJson")
     * @RequestParam(name="layer_numbers", nullable=false, strict=false, description="Affected layers, 0-based number")
     * @RequestParam(name="well_type", nullable=false, strict=false, description="For well boundary, which type of well.
     *     Available Well-Types:
     *          Private Well: prw
     *          Public Well: puw
     *          Observation Well: ow
     *          Industrial Well: iw
     *     ")
     *
     * @Rest\Put("/boundaries/{id}")
     * @param $id
     * @param ParamFetcher $paramFetcher
     * @return View
     */
    public function putBoundariesAction($id, ParamFetcher $paramFetcher)
    {
        $manager = $this->get('inowas.modflow.boundarymanager');
        $boundary = $manager->findById($id);

        if (! $boundary instanceof Boundary){
            throw $this->createNotFoundException(sprintf('Boundary with id=%s not found.', $id));
        }

        if ($paramFetcher->get('name')){
            $boundary->setName($paramFetcher->get('name'));
        }

        if ($paramFetcher->get('geometry') && method_exists($boundary, 'setGeometry')){
            $geometry = \geoPHP::load($paramFetcher->get('geometry'), 'json');
            switch (strtolower(get_class($geometry))){
                case "polygon":
                    $geometry = new Polygon($geometry->asArray());
                    break;
                case "linestring":
                    $geometry = new LineString($geometry->asArray());
                    break;
                case "point":
                    $geometry = new Point($geometry->asArray());
                    break;
            }

            $boundary->setGeometry($geometry->setSrid(4326));
        }

        if ($paramFetcher->get('layer_numbers') && method_exists($boundary, 'setLayerNumbers')){
            $boundary->setLayerNumbers($paramFetcher->get('layer_numbers'));
        }

        if ($paramFetcher->get('well_type') && method_exists($boundary, 'setWellType')){
            $boundary->setWellType($paramFetcher->get('well_type'));
        }

        $view = View::create($boundary)
            ->setStatusCode(200)
            ->setSerializationContext(SerializationContext::create()
                ->setGroups(array('details'))
            )
        ;

        return $view;
    }

    /**
     * Returns all boundaries from a Model specified by model id.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns all boundaries from a Model specified by model id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the model is not found"
     *   }
     * )
     *
     * @Rest\Get("/models/{id}/boundaries")
     * @param $id
     * @return View
     * @throws NotFoundHttpException
     */
    public function getModelBoundariesAction($id)
    {
        $boundaries = $this->get('inowas.modflow.boundarymanager')->findByModelId($id);
        $view = View::create($boundaries)
            ->setStatusCode(200)
            ->setSerializationContext(SerializationContext::create()
                ->setGroups(array('details'))
            )
        ;

        return $view;
    }

    /**
     * Returns boundaries from a Model specified by model id and boundary type.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Returns all boundaries from a Model specified by model id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the model is not found"
     *   }
     * )
     *
     * @Rest\Get("/models/{id}/boundaries/{type}")
     * @param Uuid $id The Uuid of the Model
     * @param string $type Possible types are: chd, ghb, rch, riv, wel
     * @return View
     * @throws NotFoundHttpException
     */
    public function getModelBoundariesByTypeAction($id, $type)
    {
        $allBoundaries = $this->get('inowas.modflow.boundarymanager')->findByModelId($id);

        $targetInstance = null;
        switch ($type){
            case 'chd':
                $targetInstance = ConstantHeadBoundary::class;
                break;
            case 'ghb':
                $targetInstance = GeneralHeadBoundary::class;
                break;
            case 'rch':
                $targetInstance = RechargeBoundary::class;
                break;
            case 'riv':
                $targetInstance = RiverBoundary::class;
                break;
            case 'wel':
                $targetInstance = WellBoundary::class;
                break;
            default:
                throw new InvalidArgumentException(sprintf('Boundary from type %s not found.', $type));
        }

        $boundaries = new ArrayCollection();
        foreach ($allBoundaries as $boundary){
            if ($boundary instanceof $targetInstance){
                $boundaries->add($boundary);
            }
        }

        $view = View::create($boundaries)
            ->setStatusCode(200)
            ->setSerializationContext(SerializationContext::create()
                ->setGroups(array('details'))
            )
        ;

        return $view;
    }

    /**
     * Add a new boundary to the model.
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Add a new boundary to the model.",
     *   statusCodes = {
     *     200 = "Returned when successful"
     *   }
     * )
     *
     * @Rest\Post("/models/{id}/boundaries")
     * @param $id
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="type", nullable=false, strict=true, description="BoundaryType. Available types are: chd, ghb, rch, riv, wel")
     * @RequestParam(name="name", nullable=false, strict=true, description="Name of the new Boundary.")
     *
     * @return View
     */
    public function postModflowModelBoundariesAction($id, ParamFetcher $paramFetcher)
    {
        $modelManager = $this->get('inowas.modflow.modelmanager');

        /** @var Boundary $boundary */
        $boundary = $this->get('inowas.modflow.boundarymanager')
            ->create($paramFetcher->get('type'));

        $boundary->setName($paramFetcher->get('name'));

        $model = $modelManager->findById($id);
        $model->addBoundary($boundary);
        $modelManager->update($model);

        $view = View::create($boundary)
            ->setStatusCode(200)
            ->setSerializationContext(SerializationContext::create()
                ->setGroups(array('details'))
            )
        ;

        return $view;
    }
}
