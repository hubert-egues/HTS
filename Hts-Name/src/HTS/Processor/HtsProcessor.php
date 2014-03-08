<?php
namespace HTS\Processor;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;
use Goutte\Client;


class HtsProcessor{
	private $crawler;
	private $client;
	private $hts;
	private $uri;

	public function __construct($uri=null){
		$this->client = new Client();
		$this->uri = "{$uri}";
	}

	public function getHtsName(){
		//Buscador
		$this->crawler = $this->client->request('POST', "{$this->uri}",array(
    	'lookfor' => '01',
    	'Phase' => 'List_items',
    	'SUBMIT'=>'List_items'
		));
/*
		$nodeValues = $this->crawler->filter('input')->each(function ($node, $i) {
		    echo $node->getAttribute('value')."\n";
		});
*/
		//$nodeValues = $this->crawler->filter('td > strong')->each(function ($node, $i) {
		$nodeValues = $this->crawler->filter('td')->each(function ($node, $i) {
			$texto = $node->nodeValue;
			if($i<100){
				$texto=trim($texto);
				if(strlen($texto)>0){
					$pos = strpos($texto, $value="(Expired)");
					if ($pos === false) {
						echo $i." - ".$node->nodeValue."\n";
					}
				}	
			}
		});


		//$this->crawler = $this->crawler->filter('body > p');
		//echo print_r($this->crawler);



		//$tableHTML = \DOMDocument::loadHTML($this->client->getResponse());
		//$this->processExpired($tableHTML);

		//echo $tableHTMLM;
		
		return "hola";
		//return $this->client->getResponse();
		//return print_r($this->client->getResponse());
	}
	private function processList(){

		//$form = $this->crawler->selectButton('List_items')->form();
    	//'name' => '01',));
    	//$uri = $form->getUri();
    	//echo $uri;
    	//return print_r($this->client->getResponse());
	}

	private function processExpired(\DOMDocument $document){
		
		$elements = $document->getElementsByTagName('input');

		if (!is_null($elements)) {
		  foreach ($elements as $element) {
		    echo "<br/>". $element->nodeName. ": ";

		    $nodes = $element->childNodes;
		    foreach ($nodes as $node) {
		      echo $node->nodeValue. "\n";
		    }
		  }
		}
		return $elements;
		
	}

}
