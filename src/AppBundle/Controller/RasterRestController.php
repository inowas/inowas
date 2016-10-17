<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Raster;
use AppBundle\Model\BoundingBox;
use AppBundle\Model\GridSize;
use AppBundle\Model\PropertyFactory;
use AppBundle\Model\PropertyTimeValueFactory;
use AppBundle\Model\PropertyTypeFactory;
use AppBundle\Model\PropertyValueFactory;
use AppBundle\Model\RasterFactory;

use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\View\View;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Ramsey\Uuid\Uuid;

class RasterRestController extends FOSRestController
{

    /**
     * Return an Raster by id
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return a raster by id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
     *
     * @param string $id raster
     *
     * @return View
     */
    public function getRasterAction($id)
    {
        try{
            $uuid = Uuid::fromString($id);
        } catch (\InvalidArgumentException $e) {
            throw $this->createNotFoundException('Raster with id='.$id.' not found.');
        }

        $entity = $this->getDoctrine()
            ->getRepository('AppBundle:Raster')
            ->findOneBy(array(
                'id' => $uuid
            ));

        if (! $entity instanceof Raster) {
            throw $this->createNotFoundException('Raster with id='.$id.' not found.');
        }

        $view = View::create();
        $view->setData($entity)
            ->setStatusCode(200)
            ->setSerializationContext(SerializationContext::create()
                ->setGroups(array('rasterdetails')));

        return $view;
    }

    /**
     * Return an area by id
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Return an area by id",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     404 = "Returned when the user is not found"
     *   }
     * )
    /**
     * Creates a new Ticket from the submitted data.<br/>
     *
     * @ApiDoc(
     *   resource = true,
     *   description = "Creates a new Ticket from the submitted data.",
     *   statusCodes = {
     *     200 = "Returned when successful",
     *     400 = "Returned when the form has errors"
     *   }
     * )
     *
     * @param ParamFetcher $paramFetcher
     *
     * @RequestParam(name="id", nullable=false, strict=false, description="ModelObject-Id in which property to store the results")
     * @RequestParam(name="propertyName", nullable=false, strict=true, description="Name of Property", default="Result")
     * @RequestParam(name="propertyType", nullable=false, strict=true, description="Name of PropertyType")
     * @RequestParam(name="numberOfColumns", nullable=false, strict=true, description="Number of columns")
     * @RequestParam(name="numberOfRows", nullable=false, strict=true, description="Number of rows")
     * @RequestParam(name="upperLeftX", nullable=false, strict=true, description="UpperLeft corner X")
     * @RequestParam(name="upperLeftY", nullable=false, strict=true, description="UpperLeft corner Y")
     * @RequestParam(name="lowerRightX", nullable=false, strict=true, description="UpperLeft corner X")
     * @RequestParam(name="lowerRightY", nullable=false, strict=true, description="UpperLeft corner Y")
     * @RequestParam(name="srid", nullable=false, strict=true, description="SRID", default=4326)
     * @RequestParam(name="data", nullable=false, strict=true, description="Data in JSON-Format")
     * @RequestParam(name="description", nullable=false, strict=true, description="Description", default="")
     * @RequestParam(name="noDataVal", nullable=false, strict=true, description="Data in JSON-Format", default=-999)
     * @RequestParam(name="date", nullable=true, strict=true, description="Date, default null", default=null)
     *
     * @return View
     */
    public function postRasterAction(ParamFetcher $paramFetcher)
    {
        $modelObjectId = $paramFetcher->get('id');

        try{
            $uuid = Uuid::fromString($modelObjectId);
        } catch (\InvalidArgumentException $e){
            throw $this->createNotFoundException('ModelObject with id='.$modelObjectId.' not found.');
        }

        $mo = $this->getDoctrine()
            ->getRepository('AppBundle:ModelObject')
            ->findOneBy(array(
                'id' => $uuid
            ));

        if (!$mo) {
            throw $this->createNotFoundException('ModelObject with id='.$paramFetcher->get('id').' not found.');
        }

        /*
         * Let's create a RasterObject
         */
        $raster = RasterFactory::create();
        $raster
            ->setGridSize(new GridSize(
                $paramFetcher->get('numberOfColumns'),
                $paramFetcher->get('numberOfRows')
            ))
            ->setBoundingBox(new BoundingBox(
                $paramFetcher->get('upperLeftX'),
                $paramFetcher->get('lowerRightX'),
                $paramFetcher->get('lowerRightY'),
                $paramFetcher->get('upperLeftY'),
                $paramFetcher->get('srid')
            ))
            ->setData(json_decode($paramFetcher->get("data")))
            ->setNoDataVal($paramFetcher->get('noDataVal'))
            ->setDescription($paramFetcher->get('description'))
        ;

        /* Let's create a property and a value-object */
        $property = PropertyFactory::create()
            ->setName($paramFetcher->get('propertyName'))
            ->setPropertyType(PropertyTypeFactory::create($paramFetcher->get('propertyType')));

        if (is_null($paramFetcher->get('date'))) {
            $value = PropertyValueFactory::create();
        } else {
            $value = PropertyTimeValueFactory::createWithTime(new \DateTime($paramFetcher->get('date')));
        }
        
        $this->getDoctrine()->getManager()->persist($raster);
        $this->getDoctrine()->getManager()->flush();

        $mo->addProperty($property);
        $property->addValue($value);
        $value->setRaster($raster);

        $this->getDoctrine()->getManager()->persist($raster);
        $this->getDoctrine()->getManager()->persist($value);
        $this->getDoctrine()->getManager()->persist($property);
        $this->getDoctrine()->getManager()->persist($mo);
        $this->getDoctrine()->getManager()->flush();

        $view = View::create();
        $view->setData($raster)->setStatusCode(200);

        return $view;
    }
}