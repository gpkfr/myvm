<?php namespace Gpkfr\Myvm;

use Symfony\Component\Process\Process;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
//use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * InitCommand
 */
class InitCommand extends Command
{
  /**
  * Configure the command options.
  *
  * @return void
  */
  protected function configure()
  {
    $this->setName('init')
      ->setDescription('create new vm')
      ->addArgument(
        'project_name',
        InputArgument::REQUIRED,
        'Indicate Project Name'
    );
  }

  /**
  * Execute the command.
  *
  * @param  \Symfony\Component\Console\Input\InputInterface  $input
  * @param  \Symfony\Component\Console\Output\OutputInterface  $output
  * @return void
  */
  public function execute(InputInterface $input, OutputInterface $output)
  {
    $project_name = $input->getArgument('project_name');

    if (! $project_name)
    {
      throw new \InvalidArgumentException("I need a Project Name.");
    }

    $project_dir = getcwd()."/".$project_name;

    if (is_dir($project_dir))
    {
      throw new \InvalidArgumentException("$project_name directory already exist.");
    }

    // Clone the repository

    $Pclone = new Process('git clone https://github.com/gpkfr/GenericVM.git '.$project_name, getcwd(), null, null, null);

    $Pclone->run(function($type, $line) use ($output)
    {
      $output->write($line);
    });

    $output->writeln('<comment>Cloning in '.$project_dir.' Directory...</comment> <info>✔</info>');

    //init submodule
    $Pinit = new Process('git submodule init', $project_dir, null, null, null);
    $Pinit->disableOutput();
    $Pinit->run();
    $output->writeln('<comment>Init submodule in '.$project_dir.' Directory...</comment> <info>✔</info>');

    //update submodule
    $Pupdate = new Process('git submodule update', $project_dir, null, null, null);
    $Pupdate->disableOutput();
    $Pupdate->run();
    $output->writeln('<comment>Submodule updated ...</comment> <info>✔</info>');


    if (! is_file(getcwd().DIRECTORY_SEPARATOR.$project_name.DIRECTORY_SEPARATOR.'config.yaml')) {
      copy(getcwd().DIRECTORY_SEPARATOR.$project_name.DIRECTORY_SEPARATOR.'config.yaml.sample',
        getcwd().DIRECTORY_SEPARATOR.$project_name.DIRECTORY_SEPARATOR.'config.yaml');

      $output->writeln('<comment>Creating config.yaml file...</comment> <info>✔</info>');
      $output->writeln('<comment>config.yaml file created at:</comment> '.getcwd().DIRECTORY_SEPARATOR.$project_name.DIRECTORY_SEPARATOR.'config.yaml');
      $output->writeln('<comment>First, you need to edit your config file then launch your vm with vagrant up...</comment>');
    }
  }



}
