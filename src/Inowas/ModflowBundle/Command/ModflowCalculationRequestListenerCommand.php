<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Command;

use Inowas\Common\Id\UserId;
use Inowas\ModflowModel\Model\AMQP\ModflowCalculationRequest;
use Inowas\ModflowModel\Model\Command\CalculateModflowModel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModflowCalculationRequestListenerCommand extends ContainerAwareCommand
{

    /** @var  UserId */
    protected $ownerId;

    protected function configure(): void
    {
        // Name and description for app/console command
        $this
            ->setName('inowas:calculation:request:listener')
            ->setDescription('Listener which receives calculation-requests')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $callback = function($msg) {
            echo ' [+] Submitting async calculation request', "\n";
            echo '  Receiving:'. $msg->body ."\n";

            try {
                $request = ModflowCalculationRequest::fromArray(json_decode($msg->body, true));
                $this->getContainer()->get('prooph_service_bus.modflow_command_bus')->dispatch(
                    CalculateModflowModel::forModflowModelWitUserId($request->userId(), $request->modelId())
                );

            } catch (\Exception $exception) {
                echo sprintf($exception->getMessage());
            }

            echo ' [x] Done', "\n";
            $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
        };

        $listener = $this->getContainer()->get('inowas.modflowmodel.amqp_modflow_calculation_request_listener');
        $listener->listen($callback);
    }
}
