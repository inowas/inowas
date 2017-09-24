<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Inowas\ModflowBundle\Exception\InvalidArgumentException;
use Inowas\ModflowModel\Model\Command\AddBoundary;
use Inowas\ModflowModel\Model\Command\AddLayer;
use Inowas\ModflowModel\Model\Command\CalculateModflowModel;
use Inowas\ModflowModel\Model\Command\CalculateStressPeriods;
use Inowas\ModflowModel\Model\Command\CloneModflowModel;
use Inowas\ModflowModel\Model\Command\CreateModflowModel;
use Inowas\ModflowModel\Model\Command\DeleteModflowModel;
use Inowas\ModflowModel\Model\Command\RemoveBoundary;
use Inowas\ModflowModel\Model\Command\RemoveLayer;
use Inowas\ModflowModel\Model\Command\RequestModflowModelCalculation;
use Inowas\ModflowModel\Model\Command\UpdateBoundary;
use Inowas\ModflowModel\Model\Command\UpdateLayer;
use Inowas\ModflowModel\Model\Command\UpdateModflowModel;
use Inowas\ModflowModel\Model\Command\UpdateStressPeriods;
use Inowas\ScenarioAnalysis\Model\Command\CloneScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenario;
use Inowas\ScenarioAnalysis\Model\Command\CreateScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\Command\DeleteScenario;
use Inowas\ScenarioAnalysis\Model\Command\DeleteScenarioAnalysis;
use Inowas\ScenarioAnalysis\Model\Command\UpdateScenarioAnalysis;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Prooph\Common\Messaging\Message;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/** @noinspection LongInheritanceChainInspection */
class MessageBoxController extends InowasRestController
{

    private $whiteList = [
        'addBoundary' => AddBoundary::class,
        'addLayer' => AddLayer::class,
        'calculateModflowModel' => CalculateModflowModel::class,
        'calculateStressPeriods' => CalculateStressPeriods::class,
        'cloneModflowModel' => CloneModflowModel::class,
        'cloneScenarioAnalysis' => CloneScenarioAnalysis::class,
        'createModflowModel' => CreateModflowModel::class,
        'createScenarioAnalysis' => CreateScenarioAnalysis::class,
        'createScenario' => CreateScenario::class,
        'deleteModflowModel' => DeleteModflowModel::class,
        'deleteScenario' => DeleteScenario::class,
        'deleteScenarioAnalysis' => DeleteScenarioAnalysis::class,
        'removeBoundary' => RemoveBoundary::class,
        'removeLayer' => RemoveLayer::class,
        'requestModflowModelCalculation' => RequestModflowModelCalculation::class,
        'updateBoundary' => UpdateBoundary::class,
        'updateLayer' => UpdateLayer::class,
        'updateModflowModel' => UpdateModflowModel::class,
        'updateScenarioAnalysis' => UpdateScenarioAnalysis::class,
        'updateStressPeriods' => UpdateStressPeriods::class,
    ];

    /**
     * messageBox
     *
     * @ApiDoc(
     *      input = {
     *          "class" = "AppBundle\Dto\ProophMessageApi",
     *      },
     *      resource = true,
     *      section = "MessageBox",
     *      description = "Send a message to the app",
     *      statusCodes = {
     *          202 = "Returned when successful",
     *          500 = "Error / JSON-Schema validation failed"
     *      }
     * )
     *
     *
     * @Rest\Post("/messagebox")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Prooph\ServiceBus\Exception\CommandDispatchException
     * @throws \Inowas\ModflowBundle\Exception\InvalidArgumentException
     */
    public function messageBoxAction(Request $request): JsonResponse
    {
        $messageFactory = $this->get('prooph_message_factory');
        $content = $this->getContentAsArray($request);

        $this->assertContainsKey('message_name', $content);
        $this->assertContainsKey('payload', $content);

        if (!array_key_exists($content['message_name'], $this->whiteList)) {
            throw InvalidArgumentException::withMessage(sprintf(
                'Submitted messageName \'%s\' not known. Available names are: %s.', $content['message_name'], implode(', ', array_keys($content))
            ));
        }

        if (isset($content['created_at'])) {
            $content['created_at'] = $this->getDatetime($content['created_at']);
        }

        $commandClass = $this->whiteList[$content['message_name']];
        $content['message_name'] = $commandClass;

        $message = $messageFactory->createMessageFromArray($commandClass, $content);
        $message = $message->withAddedMetadata('user_id', $this->getUserId()->toString());

        switch ($message->messageType()) {
            case Message::TYPE_COMMAND:
                $this->get('prooph_service_bus.modflow_command_bus')->dispatch($message);
                return new JsonResponse('', 202);

            case Message::TYPE_EVENT:
                $this->get('prooph_service_bus.modflow_event_bus')->dispatch($message);
                return new JsonResponse('', 202);

            case Message::TYPE_QUERY:
                throw InvalidArgumentException::withMessage('Invalid message type. QueryBus not supported.');

            default:
                throw InvalidArgumentException::withMessage('Invalid message type.');
        }
    }

    private function getDatetime($date): \DateTimeImmutable
    {
        if (is_string($date)) {
            return \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s.u',
                $date,
                new \DateTimeZone('UTC')
            );
        }

        if (is_array($date)) {
            return \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s.u',
                $date['date'],
                new \DateTimeZone('UTC')
            );
        }

        throw InvalidArgumentException::withMessage('Invalid date format in message.');
    }
}
