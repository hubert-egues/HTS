<?php

namespace HTS\Processor;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;
use Goutte\Client;
use HTS\Factory\HTSFactory;


class HtsProcessor
{
    private $crawler;
    private $hts;
    private $htsNames;
    /* for log */
    private $knowUnits;
    private $unitUsed;
    private $knowFeatures;
    private $knowValues;
    private $valuesUsed;
    private $unknown;
    private $withoutQty;
    private $currentChapter;
    private $conditions;
    private $unitOfQuantity;
    

    public function __construct($uri=null)
    {
        $client = new Client();
        $this->crawler =  $client->request('GET', "{$uri}");
        $this->htsNames = new \ArrayObject();
        //$this->htsNames->append($this->parseNames());
        $this->hts = new \ArrayObject();
        
        /* log */
        $this->knowUnits = array();
        $this->knowValues = array();
        $this->unknown = array();
		$this->withoutQty = array();
		$this->unitUsed = array();
		$this->conditions = array();
		$this->currentChapter = 0;
		$this->valuesUsed = array();
		$this->unitOfQuantity = array();
		$this->knowFeatures = array();
    }

    public function catchLinksChapters()
    {	
    	$not_exists = array(77, 98);
    	/* 1 -> 98 */
    	$start = 1;
    	$finish = 98;
        for ($i = $start; $i <= $finish; $i++) {

        	if (in_array($i, $not_exists)) {
        		continue;
        	}

			$this->setChapter($i);
        	print("CHAPTER ".$i."\n");
            $link = $this->crawler->selectLink("Chapter {$i}")->link();
            $tableXML = new \DOMDocument('1.0', 'UTF-8');
            $uriToChapter = $link->getUri();                        
            $result = $tableXML->load($uriToChapter);

			if ($result) {
            	$this->processEightDigitRow($tableXML);
            	$this->processSpecialEightDigitRow($tableXML);
            	$this->processTenDigitRow($tableXML);
            } else {
            	print("DOCUMENTO NO VALIDO ".$i);
            }

            $this->hts->ksort();
			if ($i == $finish) {
				break;
			}  
            sleep(5);
        }

        //return $this->parseNames();
        $hts_scraping = json_encode($this->hts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $fp = fopen("hts.json","w+");
        fwrite($fp, $hts_scraping);
        fclose($fp);

		$this->saveLog();

        return $hts_scraping;
    }
    
    private function setChapter($chapter) {
    	$this->currentChapter = $chapter;
    	$this->knowUnits[$this->currentChapter] = array();
    	$this->knowValues[$this->currentChapter] = array();
    	$this->unknown[$this->currentChapter] = array();
    	$this->withoutQty[$this->currentChapter] = array();
    	$this->unitUsed[$this->currentChapter] = array();
    	$this->conditions[$this->currentChapter] = array();
    	$this->valuesUsed[$this->currentChapter] = array();
    	$this->unitOfQuantity[$this->currentChapter] = array();
    	$this->knowFeatures[$this->currentChapter] = array();
    }
    
    private function saveLog() {
    	$fpl = fopen("hts_log.json","w+");
        $logKnowUnits = json_encode($this->knowUnits, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        fwrite($fpl, "KNOW UNITS");
        fwrite($fpl, $logKnowUnits);
        
        $logKnowFeatures = json_encode($this->knowFeatures, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        fwrite($fpl, "KNOW FEATURES");
        fwrite($fpl, $logKnowFeatures);

        $logKnowValues = json_encode($this->knowValues, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        fwrite($fpl, "KNOW VALUES");
        fwrite($fpl, $logKnowValues);
        $logUnknown = json_encode($this->unknown, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        fwrite($fpl, "UNKNOWN SPECIAL VALUES");
        fwrite($fpl, $logUnknown);
        $logWithoutQty = json_encode($this->withoutQty, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        fwrite($fpl, "WITHOUT QUANTITY");
        fwrite($fpl, $logWithoutQty);

		$logUnitOfQuantity = json_encode($this->unitOfQuantity, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        fwrite($fpl, "UNIT OF QUANTITY");
        fwrite($fpl, $logUnitOfQuantity);
        
        $logUnitUsed = json_encode($this->unitUsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        fwrite($fpl, "UNIT USED");
        fwrite($fpl, $logUnitUsed);
        
        $logValuesUsed = json_encode($this->valuesUsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        fwrite($fpl, "VALUES USED");
        fwrite($fpl, $logValuesUsed);
        
        $logConditions = json_encode($this->conditions, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        fwrite($fpl, "CONDITIONS");
        fwrite($fpl, $logConditions);
        
        fclose($fpl);
    }

    private function processEightDigitRow(\DOMDocument $document)
    {
        $elements = $document->getElementsByTagName('eight_digit_row');
        $count = 0;
        while (is_object($element = $elements->item($count))) {
            $insertValue = $this->processHTS($element);
            $this->hts->offsetSet($insertValue["code"], $insertValue);
            $count++;
        }
    }

    private function processSpecialEightDigitRow(\DOMDocument $document)
    {
        $elements = $document->getElementsByTagName('eight_digit_with_00_row');
        $count = 0;
        while(is_object($element = $elements->item($count))) {
            $insertValue = $this->processHTS($element);
            $this->hts->offsetSet($insertValue["code"], $insertValue);
            $count++;
        }
    }

    private function processTenDigitRow(\DOMDocument $document)
    {
        $elements = $document->getElementsByTagName('ten_digit_row');
        $count = 0;
        while(is_object($element = $elements->item($count))) {
            $insertValue = $this->processHTS($element, true, true);
            $this->hts->offsetSet($insertValue["code"], $insertValue);
            $count++;
        }
    }

    /**private function parseNames()
    {
        $data = file_get_contents(__DIR__."/hts_names.json");
        $data = '"'.$data.'"';
        return $data;
    }**/

    private function processHTS(\DOMNode $element, $haveDescription=false, $isTenRow=false)
    {
        $hts = array();
        foreach ($element->childNodes as $child) {
            switch ($child->nodeName) {
                case 'htsno':
                    $hts["code"] = str_replace('.','',$child->nodeValue);
                    $hts["hs"]   = substr($hts["code"], 0, -2);
                    break;
                case 'hts10no':
                    $code = (strlen($child->nodeValue) == 1) ? $child->nodeValue.'0' : $child->nodeValue;
                    $hts["code"] .= $code;
                    break;
                case 'description':
                    $hts["name"] = ($haveDescription == true) ? trim($child->nodeValue) : 'Not available';
                    $hts["name"] = trim($hts["name"]);
                    break;
                case 'unit':
                    $hts["quantity"] = array($this->parseUnitOfQuantity($child->nodeValue));
                    break;
                case 'units':
                    $units = array();
                    foreach ($child->childNodes as $unit) {
                        if ($unit->nodeName == 'unit') {
                            $units[] = $this->parseUnitOfQuantity($unit->nodeValue);
                        }
                    }
                    $hts["quantity"] = (empty($units)) ? 'None' : $units;
                    break;
                case 'mfn_tariff':
                    $tariffs = $this->processTariffValue($child->nodeValue);
                    $hts["tariff_all"]  = (isset($tariffs)) ? $tariffs : 'None';
                    $hts["tariff"]  = 'None';
                    break;
                case 'special':
                	$tariffs = $this->getSpecialTariffs($child->childNodes, $hts['code']);
					/* $tariffs = $this->processTariff($child->childNodes); */
                    $hts["tariff"] = (empty($tariffs)) ? $hts["tariff"] : $tariffs;
                    break;
            }
            
        }
        $fatherCode = substr($hts["code"], 0, -2);

        if ($isTenRow == true && !$this->selfParent($fatherCode)) {

            $father = ($this->hts->offsetExists($fatherCode)) ?
                          $this->hts->offsetGet($fatherCode) :
                          $this->hts->offsetGet($fatherCode .'00');

            $hts["tariff"] = $father["tariff"];
            $hts["tariff_all"] = $father["tariff_all"];


			if (array_key_exists('quantity', $hts)) {
	            if (!array_key_exists('quantity',$father) && $hts["quantity"] != 'None') {
	                $this->hts->offsetUnset($father["code"]);
	                $father["quantity"] = $hts["quantity"];
	                //TODO: process father tariff, insert targetValues based on quantities
	                $this->hts->offsetSet($father["code"], $father);
	            }
            } else {
            	/* log */
            	$this->withoutQty[$this->currentChapter][] = $hts;
            }

            return $this->cleanHtsAttrs($hts);
        }
        return $this->cleanHtsAttrs($hts);
    }

	private function parseUnitOfQuantity($unitOfQuantity) {
		$unitOfQuantity = $this->removeInvalidCharacters($unitOfQuantity);
		if ($unitOfQuantity == 'doz.' 
		|| $unitOfQuantity == 'dz.'
		|| $unitOfQuantity == 'dz'
		) {
			$unitOfQuantity = 'doz';
		} else if ($unitOfQuantity == 'No.') {
			$unitOfQuantity = 'No';
		} else if ($unitOfQuantity == 'Kg') {
			$unitOfQuantity = 'kg';
		} else if ($unitOfQuantity == 'liters liters') {
			$unitOfQuantity = 'liter';
		} else if ($unitOfQuantity == 'liters') {
			$unitOfQuantity = 'liter';
		} else if ($unitOfQuantity == 'pf. liters') {
			$unitOfQuantity = 'pf. liter';
		} else if ($unitOfQuantity == 'pf. liters') {
			$unitOfQuantity = 'pf. liter';
		} else if ($unitOfQuantity == 'Jwls.') {
			$unitOfQuantity = 'jewel';
		} else if ($unitOfQuantity == 'Dutiable Jwls.') {
			$unitOfQuantity = 'dutiable jewel';
		} else if ($unitOfQuantity == 'ofmovements') {
			$unitOfQuantity = 'of movements';
		} else if ($unitOfQuantity == 'X.') {
			$unitOfQuantity = 'X';
		} else if ($unitOfQuantity == 'Hundreds') {
			$unitOfQuantity = 'hundred';
		} else if ($unitOfQuantity == 'M2') {
			$unitOfQuantity = 'm2';
		} else if ($unitOfQuantity == 'lin.') {
			$unitOfQuantity = 'linear';
		}
		return $unitOfQuantity;
	}

	private function cleanHtsAttrs($hts) {
		if (array_key_exists('quantity', $hts)) {
			for ($i=0; $i < count($hts['quantity']); $i++) {
				$hts['quantity'][$i] = trim(trim($hts['quantity'][$i], "\n"));
				
				/* log */
				if (!in_array($hts['quantity'][$i], $this->unitOfQuantity[$this->currentChapter])) {
			    	$this->unitOfQuantity[$this->currentChapter][] = $hts['quantity'][$i];
	    		}
	    		/* end log */
			}
		}
		return $hts;
	}

    private function selfParent($code)
    {
        if (!$this->hts->offsetExists($code) && !$this->hts->offsetExists($code.'00')) {
            return true;
        }
    }

    private function createTariffValue($value, $currency, $targetValue, $conditions=array(), $feature=Null)
    {	
        return array(
            'value'       => $value,
            'currency'    => $currency,
            'targetValue' => $targetValue,
            'conditions'  => $conditions,
            'feature' => $feature
        );
    }

	private function parseTargetValue($targetValue) {
		$result = $this->_parseTargetValue($targetValue);
		$unit = $result[0];
		$feature = (count($result) > 1) ? $result[1] : Null;

		/* log */
		if (!in_array($feature, $this->knowFeatures[$this->currentChapter])) {
	    	$this->knowFeatures[$this->currentChapter][] = $feature;
		}

		return array(
			'unit' => $unit,
			'feature' => $feature
		);
	}
		
    private function _parseTargetValue($targetValue)
    {	
    	/* log */
    	if (!in_array($targetValue, $this->knowUnits[$this->currentChapter])) {
    		$this->knowUnits[$this->currentChapter][] = $targetValue;
    	}
    	$targetValue = $this->removeInvalidCharacters($targetValue);

    	if (stripos($targetValue, $value='lin. m') > -1) {
            return array('linear m');
        }
        if (strpos($targetValue, $value='No') > -1) {
            return array('No');
        }
        if (stripos($targetValue, $value='head') > -1) {
            return array('No');
        }
        if (stripos($targetValue, $value='each') > -1) {
            return array('No');
        }
        if (stripos($targetValue, $value='1000') > -1) {
            return array('c/1000');
        }
        if (stripos($targetValue, $value='thousand') > -1) {
            return array('c/1000');
        }
        if (stripos($targetValue, $value='1,000 pins') > -1) {
            return array('c/1000');
        }
        if (stripos($targetValue, $value='on drained weight') > -1) {/* peso de lo escurrido */
            return array('kg', 'on drained weight');
        }
        if (stripos($targetValue, $value='drained weight') > -1) {/* pero despues de escurrir */
            return array('kg', 'drained weight');
        }
        if (stripos($targetValue, $value='on contents and container') > -1){
        	return array('kg', 'on contents and container');
        }
        if (stripos($targetValue, $value='on entire contents of container') > -1) {
            return array('kg', 'on entire contents of container');
        }
        if (stripos($targetValue, $value='of total sugars') > -1) {
            return array('kg', 'total sugars');
        }
        if (stripos($targetValue, $value='on ethyl alcohol content') > -1) {
            return array('liter', 'pf. liter on ethyl alcohol content');
        }
        if (stripos($targetValue, $value='liters liters') > -1) {
            return array('liter');
        }
        if (stripos($targetValue, $value='on lead content') > -1) {
            return array('kg', 'on lead content');
        }
        if (stripos($targetValue, $value='on tungsten content') > -1) {
            return array('kg', 'on tungsten content');
        }
        if (stripos($targetValue, $value='on molybdenum content') > -1) {
            return array('kg', 'on molybdenum content');
        }
        if (stripos($targetValue, $value='on copper content') > -1) {
            return array('kg', 'on copper content');
        }
        if (stripos($targetValue, $value='on magnesium content') > -1) {
            return array('kg', 'on magnesium content');
        }
        if (stripos($targetValue, $value='m2 of recording surface') > -1) {
            return array('kg', 'of recording surface');
        } 
        if (stripos($targetValue, $value='clean') > -1) {/* clean kg "T51000000" */
            return array('kg');
        }
        if (stripos($targetValue, $value='doz') > -1) {
            return array('doz');
        }
        if (stripos($targetValue, $value='pr.') > -1) {
            return array('pair');
        }
        /* percent target values */
        if (preg_match('#on( )?(the)?( )?case( )?and( )?strap,( )?band( )?or( )?bracelet#i', $targetValue)) {
			return array('FOB', 'on the case and strap, band or bracelet');
        }
        if (preg_match('#on( )?(the)?( )?strap,( )?band( )?or( )?bracelet#i', $targetValue)) {
			return array('FOB', 'on the strap, band or bracelet');
        }
        if (preg_match('#on( )?the( )?(?P<item>(battery|case|apparatus|movement)+)#i', $targetValue, $params)) {
			return array('FOB', 'on the '.$params['item']);
        }
        if (preg_match('#on( )?the( )?movement( )?and( )?case#i', $targetValue)) {
			return array('FOB', 'on the movement and case');
        }
        if (stripos($targetValue, $value='on the entire set') > -1) {
        	return array('FOB', 'on the entire set');
        }
        if (stripos($targetValue, $value='on the value of the lead content') > -1) {
        	return array('FOB', 'on the value of the lead content');
        }
        if (stripos($targetValue, $value='on thevalue of the rifle') > -1) {
        	return array('FOB', 'on thevalue of the rifle');
        }
        if (preg_match('#on( )?the( )?value( )?of( )?the( )?rifle#i', $targetValue)) {
        	return array('FOB', 'on the value of the rifle');
        }
        if (stripos($targetValue, $value='on the value of the telescopic sight') > -1) {
        	return array('FOB', 'on the value of the telescopic sight');
        }

        if (stripos($targetValue, $value='foreach other piece or part') > -1) {
        	return array('No', 'foreach other piece or part');
        }
        if (stripos($targetValue, $value='line/ gross') > -1) {
        	return array('gross');
        }

        return array($targetValue);
    }

    private function parseValue($nodeValue)
    {
    	/* log */
    	if (!in_array($nodeValue, $this->knowValues[$this->currentChapter])) {
    		$this->knowValues[$this->currentChapter][] = $nodeValue;
    	}

		$nodeValue = $this->removeInvalidCharacters($nodeValue);
		$tariffValue = 'No available';
		
    	/*1.4606¢/kg less 0.020668¢/kg for each degree under 100 degrees (and fractionsof a degree in proportion) But not less Than 0.943854¢/kg*/    	
    	if (preg_match("#(?P<value>[\d\.]+)¢/kg less (?P<condition_value>[\d\.]+)¢/kg for each degree under 100 degrees \(.+\) But not less Than (?P<hts_min>[\d\.]+)¢/kg#i", $nodeValue, $params)) {
			
    		$value = (Double)$params['value'] / 100;
    		$condition_value = (Double)$params['condition_value'] / 100;
    		$min_value = (Double)$params['hts_min'] / 100;
    		$condition = $this->createCondition('degree', '-', $condition_value, "<", 100, 'diff', $min_value);
    		$value = (Double)$params['value'] / 100;
    		$tariffValue = $this->createTariffValue($value, '$', 'kg', array($condition));
    		
    	} else 
    	/*ejm : 1¢/kg, 2.5¢/litro */
        if (stripos($nodeValue, $value='¢')) {  
            $value .= (stripos($nodeValue,'each')) ? '' : '/';
            $values = explode($value, $nodeValue);
            $targetData = $this->parseTargetValue($values[1]);
            $target = $targetData['unit'];
            $feature = $targetData['feature'];
            $values[0] = str_replace('$','',$values[0]);

			/* condition */
            $data = $this->getConditionByTargetValue($target, $values[0]/100);
            $value = $data['value'];
            $conditions = $data['condition'];
            $target = $data['target'];

            $tariffValue = $this->createTariffValue($value, '$', $target, $conditions, $feature);

        } else
        /* ejm : 5%, 10%, 4.4% on the case andstrap, band or bracelet */
        if (stripos($nodeValue, $value='%')) {
        	$values = explode($value, $nodeValue);
        	$value = trim($values[0]);
        	$target = (trim($values[1]) == '') ? 'FOB' : trim($values[1]);
			$targetData = $this->parseTargetValue($target);
        	$target = $targetData['unit'];
        	$feature = $targetData['feature'];
        	
        	/* condition */
            $data = $this->getConditionByTargetValue($target, $value);
            $value = $data['value'];
            $conditions = $data['condition'];
            $target = $data['target'];
            	
           	$tariffValue = $this->createTariffValue($value, '%', $target, $conditions, $feature); 	
        } else
        /*  $....: $1.189/kg, $1.61 each, $1.13/m3, $1.34/1000,  $1.24/t  */
        if (preg_match('#\$(?P<value>[\d\.]+)(\/| )?(?P<unit>(kg|each|m3|1000|t))[.]*#i', $nodeValue, $params)) {
			$value = $params['value'];
			$target = $params['unit'];
			
			$targetData = $this->parseTargetValue($target);
        	$target = $targetData['unit'];
        	$feature = $targetData['feature'];
            
            /* condition */
            $data = $this->getConditionByTargetValue($target, $value);
            $value = $data['value'];
            $conditions = $data['condition'];
            $target = $data['target'];
            
            $tariffValue = $this->createTariffValue($value, '$', $target, $conditions, $feature);
        } else 
        /* ejm: 46.3 cents/liter 62.1 cents/kg, 14.2 cents/pf. liter, 36 cents/pr. */
        if (preg_match("#(?P<value>(\d|\.)+) cents/(?P<target_unit>(liter|kg|pf. liter|pr.))#i", $nodeValue, $params)) {
        	$targetData = $this->parseTargetValue($params['target_unit']);
        	$target = $targetData['unit'];
        	$feature = $targetData['feature'];
        	$value = (double)$params['value'] / 100;

        	$data = $this->getConditionByTargetValue($target, $value);
        	$value = $data['value'];
            $conditions = $data['condition'];
            $target = $data['target'];

        	$tariffValue = $this->createTariffValue($value, '$', $target, $conditions, $feature);
        } else 
        if (stripos($nodeValue, $value='Free') > -1) {
            $tariffValue = $this->createTariffValue(0, '%', 'FOB');
        } else 
        if (stripos($nodeValue, $value='amc')) {
			$this->setUnknowValue('WITHOUT VALUE IN PARSE VALUE: '.$nodeValue);	
        } else 
        if (stripos($nodeValue, $value='t') || strlen($nodeValue) == 1) {
			$this->setUnknowValue('WITHOUT VALUE IN PARSE VALUE: '.$nodeValue);
        } else 
        if (stripos($nodeValue, $value='The rate applicable to the natural juice in heading 200')) {
            $tariffValue = $this->createTariffValue(0, '%', 'FOB');
        } else 
        if (stripos($nodeValue, $value='pcs')) {
			$this->setUnknowValue('WITHOUT VALUE IN PARSE VALUE: '.$nodeValue);
        } else 
        if (stripos($nodeValue, $value='Gross')) {
			$this->setUnknowValue('WITHOUT VALUE IN PARSE VALUE: '.$nodeValue);
        } else 
        if(stripos($nodeValue, $value='dwb')) {
			$this->setUnknowValue('WITHOUT VALUE IN PARSE VALUE: '.$nodeValue);
        } else 
        if(stripos($nodeValue, $value='g
. . . . . . .F')) {
			$this->setUnknowValue('WITHOUT VALUE IN PARSE VALUE: '.$nodeValue);
        } else 
        if(stripos($nodeValue, $value='adw')) {
			$this->setUnknowValue('WITHOUT VALUE IN PARSE VALUE: '.$nodeValue);
        } else 
        if(stripos($nodeValue, $value='mÂ²')) {
			$this->setUnknowValue('WITHOUT VALUE IN PARSE VALUE: '.$nodeValue);
        } else 
        if(stripos($nodeValue, $value='thousands')) {
			$this->setUnknowValue('WITHOUT VALUE IN PARSE VALUE: '.$nodeValue);
        } else 
        if(stripos($nodeValue, $value='lin. M')){
            $this->setUnknowValue('WITHOUT VALUE IN PARSE VALUE: '.$nodeValue);
        } else 
        if(stripos($nodeValue, $value='m2 of recording surface')){
			$this->setUnknowValue('WITHOUT VALUE IN PARSE VALUE: '.$nodeValue);
        } else {
        	$this->setUnknowValue('WITHOUT MATCH IN PARSE VALUE: '.$nodeValue);
        }
        return $tariffValue;
    }

    private function processTariffValue($nodeValue, $isArray=true)
    {
        //if find more that one value (ejm: 2¢/kg + 5%)
        if(stripos($nodeValue, $value = '+')) {
            $valuesSum = explode($value, $nodeValue);
            foreach ($valuesSum as $values) {
                $valueSum[] = $this->processTariffValue($values,false);
            }
            return $valueSum;
        }
        $value = $this->parseValue($nodeValue);
        $clean_value = $this->cleanValues($value);
        return ($isArray == true) ? array('0' => $clean_value) : $clean_value;
    }
    
    private function cleanValues($values)
    {
    	if (is_array($values)) {
	    	foreach ($values as $key => $value) {
	    		if (is_string($value)) {
	    			$values[$key] = $this->removeInvalidCharacters($value);
	    		} else {
	    			$values[$key] = $value;
	    		}
	    		/* log */
	    		if ($key == 'value') {
		    		if (!in_array($values[$key], $this->valuesUsed[$this->currentChapter])) {
			    		$this->valuesUsed[$this->currentChapter][] = $values[$key];
			    	}
	    		} else if ($key == 'targetValue') {
	    			if (!in_array($values[$key], $this->unitUsed[$this->currentChapter])) {
			    		$this->unitUsed[$this->currentChapter][] = $values[$key];
			    	}
	    		} else if ($key == 'conditions') {
		    		if (!in_array($values[$key], $this->conditions[$this->currentChapter])) {
			    		$this->conditions[$this->currentChapter][] = $values[$key];
			    	}
	    		}
	    		/* end log */
	    	}
    	} else {
    		$values = $this->removeInvalidCharacters($values);
    	}
    	return $values;
    }

    private function removeInvalidCharacters($value) {
    	$whitout_special = str_replace(array("\n", "\t", "\r"), '', $value);
    	return $this->removeRepeatBlank($whitout_special);
    }

	/**
	* DEPRECATED
	*/
    /*private function processTariff(\DOMNodeList $tariffs)
    {
        $t = array();
        foreach ($tariffs as $tariff) {
            $tariff = explode(" ",$tariff->nodeValue);
            if (count($tariff) > 2) {
                $extraInfo = (stripos($tariff[1],"-")) ? explode("-",$tariff[1]) : $tariff[1];

                if (stripos($tariff[2], $country = 'PE')) {
                    $t["{$country}"] = $extraInfo;
                }

                if (stripos($tariff[1], $country = ',C,') or stripos($tariff[1], $country = ',L,')  
                or stripos($tariff[1], $country = ',K,') ) {
                    $t["CN"] = $extraInfo;
                }
            }
            //TODO: refactor!
            if (stripos($tariff[1], $country = 'PE')) {
                $t["{$country}"] = $this->processTariffValue($tariff[0],false);
            }
            //TODO: refactor!
            if (stripos($tariff[1], $country = ',C,') or stripos($tariff[1], $country = ',L,')  
                or stripos($tariff[1], $country = ',K,') ) {
                $t["CN"] = $this->processTariffValue($tariff[0],false);
            }
        }
        return $t;
    }*/
    
    private function getSpecialTariffs(\DOMNodeList $tariffs, $code) {
    	$result_tariffs = array();
    	foreach ($tariffs as $tariff) {
			$result = $this->getSpecialTariff($tariff, $code);
			$result_tariffs = $result_tariffs + $result; 	
		}	
		return $result_tariffs;
    }
    
    private function getSpecialTariff(\DOMNode $tariff, $code) {
    	$result = array();
    	$content = trim($tariff->nodeValue);

		if (stripos($content, '(') > -1) {
			$data = explode('(', $content);
			$tarrifValue = $data[0];
			$countries = $this->getCountries($data[1]);
			    	
			if (in_array('PE', $countries)) {
				$result['PE'] = $this->processTariffValue($tarrifValue, false);
			}
			if (in_array('C', $countries) or in_array('L', $countries) or in_array('K', $countries)) {
				$result["CN"] = $this->processTariffValue($tarrifValue, false);
			}
		} else {
			if (strlen(trim($content)) > 0) {
				$result[] = $this->processTariffValue($content, false);
			}
		}
		/* log */
		if (stripos($content, '-')) {
			$this->setUnknowValue(array('Content with "-"', $content));
		}
		return $result;
    }
    
    private function getCountries($data) {
    	$countriesValue = rtrim(trim($data), ')');
		$countriesValue = explode(' ', $countriesValue);
		$countries = [];
		foreach ($countriesValue as $country) {
			$country = trim($country);
			if ($country != '') {
				$countries[] = $country;
			}
		}
		return $countries;
    }
    
    private function setUnknowValue($value) {
    	if (!in_array($value, $this->unknown[$this->currentChapter])) {
    		$this->unknown[$this->currentChapter][] = $value;
    	}
    }
    
    /**
    	$unit_for_condition: "degree", 
    	$math_operation: "-",
    	$value_apply: (double)
    	$sign_compared: "=", 
    	$value_to_compare: (double)
		$hts_min: (double)
		$hts_max: (double)
    	$type: "diff", "each"
    	-	diff " diferencia entre quantity y valor del feature en el model"
    	-	each " por cuantas veces existe el valor de la condicion en el valor del feature del model

			if type == 'diff':
				factor = <$value_to_compare> - feature_value_model;
			else if type == 'each':
				factor = feature_value_model / <$value_to_compare>;
			else if type == 'greater':
				factor = (<$value_to_compare> > feature_value_model) ? 1 : 0;
			else:
				factor = 0;
	
			if factor <sign_compared> 0:
				HTS_VALUE = HTS_VALUE <operation> <value_apply> * factor
				if delta > <hts_min>:
					HTS_VALUE = <hts_min>
			Ejm:
	
			factor = 100 - feature_value_model
			if  factor > 0:
				HTS_VALUE = HTS_VALUE - (0.020668 * factor)
				if HTS_VALUE > 0.943854:
					HTS_VALUE = 0.943854:
		
    */
    private function createCondition($unit_for_condition, $math_operation, $value_apply,
    								 $sign_compared="=", $value_to_compare=Null, $type=Null,
    								 $hts_min=Null, $hts_max=Null) {
	
    	$condition = array( /* target is value of hts */
    			'unit_for_condition' => $unit_for_condition,
    			'math_operation' => $math_operation,
    			'value_to_compare' => $value_to_compare,
				'sign_compared' => $sign_compared,
    			'value_apply' => $value_apply,
    			'hts_min' => $hts_min,
    			'hts_max' => $hts_max,
    			'type' => $type
    		);

    	return $condition;
    } 
    
    private function getConditionByTargetValue($targetValue, $valueApply) {
    	$condition = array();
    	if ($targetValue == 'c/1000') {
        	$condition = array($this->createCondition('No', '+', $valueApply, ">", 1000, 'each'));
        	$targetValue = 'FOB';
        	$valueApply = 0;
        } else if ($targetValue == 't') {
        	$condition = array($this->createCondition('kg', '+', $valueApply, ">", 1000, 'each'));
        	$targetValue = 'FOB';
        	$valueApply = 0;
        } else if (preg_match('#(?P<unit>(jewel)) over (?P<quantity>\d+)#i', $targetValue, $params)) {
        	$condition = array($this->createCondition($params['unit'], '+', $valueApply, ">", $params['quantity'], 'greater'));
        	$targetValue = 'FOB';
        	$valueApply = 0;
        }

        return array(
        	'condition' => $condition,
        	'target' => $targetValue,
        	'value' => $valueApply
        );
    }


	private function removeRepeatBlank($str) {
		$parts = explode(" ", $str);
		$clean = '';
		foreach ($parts as $part) {
			if ($part != '') {
				$clean.= $part.' '; 
			}
		}
 		return trim($clean);
	}
}
