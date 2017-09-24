<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Command;

use Inowas\Common\Calculation\HeadData;
use Inowas\Common\Calculation\ResultType;
use Inowas\Common\DateTime\TotalTime;
use Inowas\Common\Grid\LayerNumber;
use Inowas\Common\Id\CalculationId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModflowGetHeadsCommand extends ContainerAwareCommand
{

    /** @var  UserId */
    protected $ownerId;

    protected function configure(): void
    {
        // Name and description for app/console command
        $this
            ->setName('inowas:model:heads')
            ->setDescription('Returns the layervalues of the last calculation')
            ->addArgument('id', InputArgument::REQUIRED, 'The modelId or the calculationId')
            ->addArgument('layer', InputArgument::REQUIRED, 'The layer')
            ->addArgument('totim', InputArgument::REQUIRED, 'The totalTime')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        if (Uuid::isValid($input->getArgument('id'))){
            $modelId = ModflowId::fromString($input->getArgument('id'));
            $calculationId = $this->getContainer()->get('inowas.modflowmodel.model_finder')->getCalculationIdByModelId($modelId);
        } else {
            $calculationId = CalculationId::fromString($input->getArgument('id'));
        }

        if (! $calculationId instanceof CalculationId) {
            $output->writeln('No calculationId found, please calculate first.');
        }

        $head = $this->getContainer()
            ->get('inowas.modflowmodel.calculation_results_finder')
            ->findHeadData(
                $calculationId,
                ResultType::fromString(ResultType::HEAD_TYPE),
                LayerNumber::fromInt((int)$input->getArgument('layer')),
                TotalTime::fromInt((int)$input->getArgument('totim'))
            );


        if (! $head instanceof HeadData) {
            $output->writeln(sprintf('No heads found found for calculation with id %s.', $calculationId->toString()));
        }

        $output->writeln(json_encode($head));
    }
}
