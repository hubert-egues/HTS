<?php
namespace HTS\Command;

use HTS\Processor\HtsProcessor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HtsCommand extends Command
{
    protected function configure()
    {
        $this->setName('hts-crawler');
        $this->setDescription('Crawling to http://hts.usitc.gov/by-chapter.html');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    	$url = 'http://hts.usitc.gov/by_chapter.html';    	
        $hts = new HtsProcessor($url);
        $result = $hts->catchLinksChapters();
        //$output->writeln(print_r($result));
        //$output->writeln($hts->catchLinksChapters());
    }
}
