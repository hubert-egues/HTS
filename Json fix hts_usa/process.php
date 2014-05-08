<?php 
class HtsProcessor
{
	//Genear nombres de Hts a partir del txt obtenido
	public function decoding_json_name($input,$output){
		$text=fopen($input,"r") or 
		die("No se pudo abrir el archivo");
		$i=0;
		while (!feof($text)){
	  		$linea=fgets($text);
		    $lineasalto=nl2br($linea);
		    	
		    $num = substr($lineasalto, 0, 8);
		    $name = substr($lineasalto, 8);
		    $vowels = array("\r", "\n", "\t","\"","\ ","/" );

		    $name = str_replace($vowels, "", $name);
		    $name = str_replace("[", "(", $name);
		    $name = str_replace("[", "(", $name);
		    $name = str_replace("<br >", "", $name);
		    $name = str_replace("},", "},<br >", $name);

		    $hts[$num]['name'] = $name;
	  	}
	  	fclose($text);

		$hts_name = json_encode($hts, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);	
	  	$fp = fopen($output,"w+");
	    fwrite($fp, $hts_name);
	  	return $hts_name;
	}
	public function order_json($input,$output){
		$str_datos = file_get_contents($input);
		$datos = json_decode($str_datos,true);
		$hts_name = json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);	
	  	$fp = fopen($output,"w+");
	    fwrite($fp, $hts_name);
	  	return $hts_name;
	}
}
	echo "Iniciando conversion ... <br>";
 	$process = new HtsProcessor();
 	//$process->decoding_json_name("input/hts_usa.txt","output/nombres.json");
 	$process->order_json("input/hts_peru.json","output/ordering.json");
 	echo "conversion Terminada";