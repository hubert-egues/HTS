<?php
namespace HTS\command;

use HTS\Processor\HtsProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class HtsCommand extends Command
{
	protected function configure(){
		$this->setName('hts');
		$this->setDescription('Scraping nombres Hts');
		$this->addArgument('name',InputArgument::OPTIONAL);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$hts = new HtsProcessor('http://dataweb.usitc.gov/scripts/tariff_current.asp');
    	$output->writeln(print_r($hts->getHtsName()));
	}
}
