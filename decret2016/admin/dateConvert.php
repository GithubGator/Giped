<?php
	require_once("../_session.php");
	require_once("../_dbclass.php"); 
	require_once("../_connect.php"); // ParamÂtres de connection et... connection
	require_once("../_fonctions.php"); 
	require_once("../_classes/calc_dates.php"); 
	require_once("../_classes/phonex.class.php"); 
	require_once("../_classes/cleID.class.php"); 
	require_once("../_classes/cleONED.inc.php"); // Cle nécessaire à la seconde anonymisation
	

	header("Content-type: text/html; charset=utf-8");
	setlocale(LC_ALL, 'fr_FR.utf8');
	//error_reporting(E_ERROR | E_PARSE);
	
	error_reporting(E_ALL ^ E_NOTICE);
	import_request_variables("GP","e_");
	ini_set('display_errors', 1);
	ini_set('max_execution_time','0');
	ini_set('memory_limit','-1');
	date_default_timezone_set('Europe/Paris');
	
	$error = array();
	// Objet dom XML pour valider le schéma
	$dom = new DomDocument();
	
 
	
	
	
	if(!isset ($e_file)){
	
		$folder = "./imports";
		$dossier = opendir($folder);
		while ($Fichier = readdir($dossier)) {
		  if ($Fichier != "." && $Fichier != "..") {
		    $nomFichier = $folder."/".$Fichier;
		    //echo $nomFichier."<BR>";
		    echo "<a href='dateConvert.php?file=".$Fichier."'>".$Fichier."</a><BR>";
		  }
		}
		closedir($dossier);
	
		exit();
	
	}else{
	
		// Sauvegarde du ficher xml
		$dateJour=date("Y-m-d");
		//$csvfile="./".$Fichier."-".$dateJour.".csv";
		$xmlNewfile= "new_".substr($e_file,0,-3)."xml";
		 
	
	}
	
	// nom du fichier
	//$nomFic="ONED-035-20120702_2012.XML";

	// Lecture du ficher xml
	//$xmlfile="./imports/".$nomFic;
	
	$xmlfile="./imports/".$e_file;
	
	// echo "Chargement du fichier XML";
	$i=0;
	//$xml = simplexml_load_file($xmlfile);
	REGISTER_LONG_CONSTANT("LIBXML_PARSEHUGE",	XML_PARSE_HUGE,CONST_CS | CONST_PERSISTENT);
	$xml = simplexml_load_file($xmlfile, 'SimpleXMLElement', LIBXML_PARSEHUGE);
	
	if (!$xml) {
		echo "Erreur lors du chargement du XML\n";
		foreach(libxml_get_errors() as $error) {
			echo "\t", $error->message;
		}
	}
	//print_r($xml); 
	
	
	$xmlstr = "";
	$xmlstr ="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
//$xmlstr .= "<SNATED xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"snated.xsd\">";
$xmlstr .= " 
    <DECRET>
	<INTRO>
		<VERSIONXSD>1.22</VERSIONXSD>
		<DATEXPORT>".date('Y-m-d')."</DATEXPORT>
	</INTRO>
	";
	
	$fp = fopen($xmlNewfile, 'w'); 
	fputs($fp, $xmlstr);
	fclose($fp); 
	
	$xmlstr= "";
	
	
	
	//echo $mesVariables;
	//print_r($tabCol);
	$i=0;
	foreach ($xml->DONNEE as $Element) {
		$xmlstr .= "<DONNEE>";
		//echo $i++."<br />";
		
		
		foreach($Element as $cle=>$val){
			
			switch ($cle) {
				case "NUMANONYM":
				case "NUMANONYMANT":
					if(trim($val != '')){
						//$newVal = substr(hash('sha1',substr((string)$val,0,20).$cleSecreteONED),0,20).substr(hash('sha1',substr((string)$val,20,20).$cleSecreteONED),0,20);
					//echo "cle ".$val."----".$newVal."<br />";
						//$tabCol[$cle]= $newVal;    // $val est un objet il faut un cast
						$tabCol[$cle]=(string)$val; // la seconde anonymisation est réalisé lors de l'import
					} else {
						$tabCol[$cle]= "ffffffffffffffffffffffffffffffffffffffff";
					}
				//echo $val."<br /> ";
				/*
				echo "<pre>";
				echo "  ".$cle."-> ".$val;
				echo "</pre>";
				*/
					break;
				
				case "ANSPERE":
				case "ANSMERE":
				case "ANSA1":
				case "ANSA2":
					$tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
					if($tabCol[$cle] == "1850" || $tabCol[$cle] == "1880"){
						//echo $cle."->".$tabCol[$cle]." valeur erron&eacute;e <br />";
						$tabCol[$cle]=""; // valeur manquante
						
					}
					break;
					
				case "DATDCPERE":
				case "DATDCMERE":
					$tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
					//$buf = $tabCol[$cle];
					// traitement des dates au format jj/mm/AAAA
					if(preg_match( '`^\d{2}/\d{2}/\d{4}$`' , $tabCol[$cle] )){
						$parts = explode("/", $tabCol[$cle] );
						
						$tabCol[$cle] = $parts[2]."-".$parts[1];	
					}else if(preg_match( '`^\d{2}-\d{2}-\d{4}$`' , $tabCol[$cle] )){
						$parts = explode("-", $tabCol[$cle] );
						
						$tabCol[$cle] = $parts[2]."-".$parts[1];	
					}else if(preg_match( '`^\d{4}/\d{2}/\d{2}$`' , $tabCol[$cle] )){
						$parts = explode("/", $tabCol[$cle] );					
						$tabCol[$cle] = $parts[0]."-".$parts[1];	
					}else if(preg_match( '`^\d{4}-\d{2}-\d{2}$`' , $tabCol[$cle] )){ // le format est correct mais il faut supprimer le jour
						$parts = explode("-", $tabCol[$cle] );					
						$tabCol[$cle] = $parts[0]."-".$parts[1];	
					}
					//echo $buf."-> ".$tabCol[$cle]."<br /> ";
					break;
				
				default :
					//echo $cle ."--->".$val."<br />";
					$tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
					// traitement des dates au format jj/mm/AAAA
					if(preg_match( '`^\d{2}/\d{2}/\d{4}$`' , $tabCol[$cle] )){
						$parts = explode("/", $tabCol[$cle] );
						
						$tabCol[$cle] = $parts[2]."-".$parts[1]."-".$parts[0];	
					}else if(preg_match( '`^\d{2}-\d{2}-\d{4}$`' , $tabCol[$cle] )){
						$parts = explode("-", $tabCol[$cle] );
						
						$tabCol[$cle] = $parts[2]."-".$parts[1]."-".$parts[0];	
					}else if(preg_match( '`^\d{4}/\d{2}/\d{2}$`' , $tabCol[$cle] )){
						$parts = explode("/", $tabCol[$cle] );					
						$tabCol[$cle] = $parts[0]."-".$parts[1]."-".$parts[2];	
					}
					break;
				
			}
		}
		
		
 
		/*
		echo "<pre>";
		print_r($tabCol);
		echo "</pre>";
		echo "<br />***************************************************<br />";
		*/
		
		// On reconstitue le ficher
		
		foreach($tabCol as $cle=>$val){
			$xmlstr .= toXML($val,$cle,1);
			// on initialise
			$tabCol[$cle]="NULL";
		}
		unset($tabCol);
		$xmlstr.= "\n</DONNEE>\n";
		$fp = fopen($xmlNewfile, 'a'); 
		fputs($fp, $xmlstr);
		fclose($fp); 
		$xmlstr= "";
		/*
		if($i == 16000)
			break;
		*/
		
	}
	
	
	$xmlstr.= "\n</DECRET>\n";
	
	//exit();
	
	/*
	// Sauvegarde du ficher xml
	$dateJour=date("Y-m-d");
	//$csvfile="./".$Fichier."-".$dateJour.".csv";
	$xmlfile= "new_".substr($e_file,0,-3)."xml";
	*/
	echo $xmlNewfile;
	
	$fp = fopen($xmlNewfile, 'a+'); 
	fputs($fp, $xmlstr);
	fclose($fp); 
		
	
	
	
	
function toXML($champ,$nomChamp="ENCOURS",$tabu=0) {
	
	if(trim($champ)=="" || $champ =="NULL")
		return "\n".toTab($tabu)."<".text_to_xml($nomChamp)." />";

	else
		return "\n".toTab($tabu)."<".text_to_xml($nomChamp).">".text_to_xml($champ)."</".$nomChamp.">";
}



function text_to_xml ($string){
    $char_bad = array(
	'<',
	'>',
	'&'
	);
    $char_good = array(
	'&lt;',
        '&gt;',
	'&amp;'
	);  
    // Correction mauvais encodage des caractères non ISO-8859-1		
    $carKo=array("\xC2\x9C","\xC2\x8C","\xC2\x80");  //"œ","Œ","€"
    $carOk=array("\xC5\x93","\xC5\x92","\xE2\x82\xAC");
    
    return  str_replace($carKo,$carOk,utf8_encode(str_replace($char_bad,$char_good,$string)));
}


function toComment($lib){
	return "<!-- ".text_to_xml($lib)." -->";
}
function toTab($n){

	switch($n){
		case 1 :
			$indent = "    ";
			break;
		case 2 :
			$indent = "        ";
			break;
		case 3 :
			$indent = "            ";
			break;
		case 4 :
			$indent = "                ";
			break;	
		case 5 :
			$indent = "                    ";
			break;
		case 6 :
			$indent = "                        ";
			break;
		case 7 :
			$indent = "                            ";
			break;			
		default :
			$indent = "";
		
	}

	return $indent;

}

function baliseXML($balise, $tabu){

	return "\n".toTab($tabu)."<".$balise.">";
}

	
	
?>
