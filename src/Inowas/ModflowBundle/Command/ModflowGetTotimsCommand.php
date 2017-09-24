<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Command;

use Inowas\Common\Id\CalculationId;
use Inowas\Common\Id\ModflowId;
use Inowas\Common\Id\UserId;
use Inowas\Common\Modflow\TotalTimes;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModflowGetTotimsCommand extends ContainerAwareCommand
{

    /** @var  UserId */
    protected $ownerId;

    protected function configure(): void
    {
        // Name and description for app/console command
        $this
            ->setName('inowas:model:times')
            ->setDescription('Returns the totalTimes of the last calculation')
            ->addArgument('id', InputArgument::REQUIRED, 'The modelId or the calculationId')
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

        $totalTimes = $this->getContainer()->get('inowas.modflowmodel.calculation_results_finder')->getTotalTimesFromCalculationById($calculationId);

        if (! $totalTimes instanceof TotalTimes) {
            $output->writeln(sprintf('No totalTimes found for calculation with id %s.', $calculationId->toString()));
        }

        $output->writeln(json_encode($totalTimes->toArray()['total_times']));
    }
}
