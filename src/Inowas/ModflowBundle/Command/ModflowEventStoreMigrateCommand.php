<?php

declare(strict_types=1);

namespace Inowas\ModflowBundle\Command;

use Inowas\Common\Id\UserId;
use Inowas\ModflowBundle\DataFixtures\Scenarios\Hanoi\Hanoi;
use Inowas\ModflowBundle\DataFixtures\Scenarios\RioPrimero\RioPrimero;
use Inowas\ModflowBundle\DataFixtures\Scenarios\RioPrimero\RioPrimeroArea;
use Inowas\ModflowBundle\DataFixtures\Scenarios\RioPrimero\RioPrimeroBaseModel;
use Inowas\ModflowBundle\DataFixtures\Scenarios\RioPrimero\RioPrimeroBaseModelAndFutureWells;
use Inowas\ModflowBundle\DataFixtures\Scenarios\SanFelipe\SanFelipe;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ModflowEventStoreMigrateCommand extends ContainerAwareCommand
{

    /** @var  UserId */
    protected $ownerId;

    protected function configure(): void
    {
        // Name and description for app/console command
        $this
            ->setName('inowas:es:migrate')
            ->setDescription('Migrates the Hanoi-Model to the Database')
            ->addArgument('model', InputArgument::OPTIONAL, 'The model which to load')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $modelname =  $input->getArgument('model');

        if (null === $modelname){
            $output->writeln('Possible Arguments are:');
            $output->writeln('1 or Hanoi for the hanoi-modflow-model');
            $output->writeln('2 or Rio Primero scenario analysis');
            $output->writeln('3 or Rio Primero base model for summer school');
            $output->writeln('4 or Rio Primero area only');
            $output->writeln('5 or Rio Primero Scenario Analysis');
            $output->writeln('6 or San Felipe for the hanoi-modflow-model');
        }

        if ($modelname === 'Hanoi Basemodel with Scenarios' || (int)$modelname === 1) {
            $hanoi = new Hanoi();
            $hanoi->setContainer($this->getContainer());
            $hanoi->load();
        }

        if ($modelname === 'Rio Primero' || (int)$modelname === 2) {
            $rioPrimero = new RioPrimero();
            $rioPrimero->setContainer($this->getContainer());
            $rioPrimero->load();
        }

        if ($modelname === 'Rio Primero base model for summer school' || (int)$modelname === 3) {
            $rioPrimero = new RioPrimeroBaseModel();
            $rioPrimero->setContainer($this->getContainer());
            $rioPrimero->load();
        }

        if ($modelname === 'Rio Primero Area' || (int)$modelname === 4) {
            $rioPrimero = new RioPrimeroArea();
            $rioPrimero->setContainer($this->getContainer());
            $rioPrimero->load();
        }

        if ($modelname === 'Rio Primero Scenario Analysis' || (int)$modelname === 5) {
            $rioPrimero = new RioPrimeroBaseModelAndFutureWells();
            $rioPrimero->setContainer($this->getContainer());
            $rioPrimero->load();
        }

        if ($modelname === 'San Felipe' || (int)$modelname === 6) {
            $rioPrimero = new SanFelipe();
            $rioPrimero->setContainer($this->getContainer());
            $rioPrimero->load();
        }
    }
}
