<?php
namespace HTS\Command;

use HTS\Scrapper\HsScrapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class WebScrapperCommand extends Command
{
    protected function configure()
    {
        $this->setName('hs-scrapping')
             ->setDescription('Scrapping to http://www.allhscodes.com/');
    }

    protected function execute( InputInterface $input, OutputInterface $output)
    {
        $hs = new HsScrapper('http://www.allhscodes.com/');
        //Crate the array, catch the categories fathers and later the songs
        //Add to database

        $crawler = $hs->getCrawler();
        //Rellena las secciones dentro de $sections
        //$sections = new \ArrayObject($hs->proccessSection());
        //$sectionIterator = $sections->getIterator();
        /**while ($sectionIterator->valid()) {
            $output->write(implode('  ',$sectionIterator->current()),true);
            $sectionIterator->next();
        }**/
        //$output->writeln("======================================================");
        //$output->writeln("======================================================");
        //$output->writeln("======================================================");
        //$chapters = new \ArrayObject();
        $categories = new \ArrayObject();
        $hs->proccessCategories($categories);
        //Rellena los capitulos dentro del objeto $chapters
        /**foreach ($sections as $section) {
            $hs->proccessChapter($section, $chapters);
        }
        //Presentacion de datos
        $chapterIterator = $chapters->getIterator();
        while ($chapterIterator->valid()) {
            $output->write(implode('  ',$chapterIterator->current()), true);
            $chapterIterator->next();
        }**/
        $categoriesIterator = $categories->getIterator();
        while($categoriesIterator->valid()) {
            $output->write(implode(' * ',$categoriesIterator->current()), true);
            $categoriesIterator->next();
        }

    }

}
