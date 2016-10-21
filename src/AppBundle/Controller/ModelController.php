<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ModflowModelScenario;
use AppBundle\Entity\ModFlowModel;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;


class ModelController extends Controller
{
    /**
     * @Route("/models/modflow/create", name="models_modflow_create")
     * @Method({"GET"})
     * @Security("has_role('ROLE_USER')")
     *
     * @return response
     */
    public function modelsModflowCreateAction()
    {
        return $this->render('inowas/model/modflow/model.create.html.twig', array(
                'apiKey' => $this->getUser()->getApiKey()
            )
        );
    }

    /**
     * @Route("/models/modflow", name="modflow_model_without_id")
     * @Route("/models/modflow/{id}", name="modflow_model")
     * @Method({"GET"})
     * @Security("has_role('ROLE_USER')")
     *
     * @param $id
     * @return Response
     */
    public function modelAction($id=null)
    {
        if (is_null($id)){
            return $this->render('inowas/model/modflow/model.lv2.html.twig', array(
                    'model' => null,
                    'user' => $this->getUser()
                )
            );
        }

        if (! Uuid::isValid($id)){
            return $this->redirectToRoute('modflow_model_list');
        }

        $model = $this->getDoctrine()->getRepository('AppBundle:ModFlowModel')
            ->findOneBy(array(
                'id' => Uuid::fromString($id)
            ));

        if (! $model instanceof ModFlowModel){
            return $this->redirectToRoute('modflow_model_list');
        }

        $scenarios = $this->getDoctrine()->getRepository('AppBundle:ModflowModelScenario')
            ->findBy(array(
                'baseModel' => $model,
                'owner' => $this->getUser()
            ), array(
                    'dateCreated' => 'ASC'
                )
            );

        foreach ($scenarios as $scenario) {
            $model->registerScenario($scenario);
        }

        return $this->render('inowas/model/modflow/model.lv2.html.twig', array(
                'model' => $model,
                'user' => $this->getUser()
            )
        );
    }

    /**
     * @Route("/models/modflow/{id}/scenarios", name="modflow_model_modflow_scenarios_list")
     * @Method({"GET"})
     * @Security("has_role('ROLE_USER')")
     *
     * @param $id
     * @return Response
     */
    public function modelsModflowScenariosAction($id)
    {
        if (! Uuid::isValid($id)){
            return $this->redirectToRoute('modflow_model_list');
        }

        $model = $this->getDoctrine()->getRepository('AppBundle:ModFlowModel')
            ->findOneBy(array(
                'id' => Uuid::fromString($id)
            ));

        if (! $model instanceof ModFlowModel){
            return $this->redirectToRoute('modflow_model_list');
        }

        $scenarios = $this->getDoctrine()->getRepository('AppBundle:ModflowModelScenario')
            ->findBy(array(
                'baseModel' => $model,
                'owner' => $this->getUser()
            ), array(
                    'name' => 'ASC'
                )
            );

        foreach ($scenarios as $scenario) {
            $model->registerScenario($scenario);
        }

        return $this->render('inowas/model/modflow/scenarios.html.twig', array(
                'baseModel' => $model,
                'scenarios' => $scenarios
            )
        );
    }

    /**
     * @Route("/models/modflow/{id}/scenarios/results", name="modflow_model_modflow_scenarios_results")
     * @Method({"GET"})
     * @Security("has_role('ROLE_USER')")
     *
     * @param $id
     * @return Response
     */
    public function modelsModflowScenariosResultsAction($id)
    {

        if (! Uuid::isValid($id)) {
            return $this->redirectToRoute('modflow_model_list');
        }

        $model = $this->getDoctrine()->getRepository('AppBundle:ModFlowModel')
            ->findOneBy(array(
                'id' => Uuid::fromString($id)
            ));

        if (null === $model){
            return $this->redirectToRoute('modflow_model_list');
        }

        $scenarios = $this->getDoctrine()->getRepository('AppBundle:ModflowModelScenario')
            ->findBy(array(
                'baseModel' => $model,
                'owner' => $this->getUser()
                ), array(
                    'dateCreated' => 'ASC'
                )
            );

        if (count($scenarios) == 0) {
            $this->redirectToRoute('modflow_model_modflow_scenarios_list', array('id' => $id));
        }

        foreach ($scenarios as $scenario) {
            $model->registerScenario($scenario);
        }

        return $this->render('inowas/model/modflow/scenarios_results.html.twig', array(
                'model' => $model
            )
        );
    }

    /**
     * @Route("/models/modflow/{modelId}/scenarios/{scenarioId}", name="modflow_model_modflow_scenario")
     * @Method({"GET"})
     * @Security("has_role('ROLE_USER')")
     *
     * @param $modelId
     * @param $scenarioId
     * @return Response
     */
    public function modelModFlowScenarioAction($modelId, $scenarioId)
    {
        if (! Uuid::isValid($modelId) || ! Uuid::isValid($scenarioId)){
            return $this->redirectToRoute('modflow_model_list');
        }

        /** @var ModflowModelScenario $scenario */
        $scenario = $this->getDoctrine()->getRepository('AppBundle:ModflowModelScenario')
            ->findOneBy(array(
                'id' => Uuid::fromString($scenarioId),
                'baseModel' => Uuid::fromString($modelId)
            ));

        if (!$scenario instanceof ModflowModelScenario){
            return $this->redirectToRoute('modflow_model_list');
        }
        
        return $this->render('inowas/model/modflow/scenario.html.twig', array(
                'scenario' => $scenario,
                'user' => $this->getUser()
            )
        );
    }
}
