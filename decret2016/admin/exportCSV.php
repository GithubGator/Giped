<?php
	require_once("../_session.php");
	require_once("../_dbclass.php"); 
	require_once("../_connect.php"); // ParamÂtres de connection et... connection
	require_once("../_fonctions.php"); 
	require_once("../_classes/calc_dates.php"); 
	require_once("../_classes/phonex.class.php"); 
	require_once("../_classes/cleID.class.php"); 
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
    return str_replace($char_bad,$char_good,$string);
}


	function export_elementcsv($data) {
		$nbVar = 0;
		$csvtr = "\n";
		
		echo "<pre>";
		print_r($data);
		echo "</pre>";
		
		foreach($data as $var=>$value){
			//if (isset($map[$field]))   $lib = $map[$field];	
				if(trim($value )=="" || $value == "0" || $value == "0000-00-00"){
					$csvstr .= "-" .";";
				}else {
					$csvstr .= "'".$value. "';";
					$nbVar++;
				}
		}	
		$csvstr = $nbVar.";".$csvstr."\n";
		return $csvstr;
	}

	header("Content-type: text/html; charset=utf-8");
	setlocale(LC_ALL, 'fr_FR.utf8');
	//error_reporting(E_ERROR | E_PARSE);
	error_reporting(E_ALL);
	import_request_variables("GP","e_");
	//
	
	// Période concernée
	$debutExport = date("Y",strtotime("-1 year"))."-01-01"; 
	$finExport =  date("Y")."-05-01"; 

	$reqUpdate="";
	$reqSelect="";

	
	// liste des variables du decret
	$reqSelect = "select NEWFIELD, chVar from nomenclature  where export =1 and (KEEP = 1 OR KEEP = 2) ORDER BY `numVar`";
	$result = ExecRequete($reqSelect, $maConnection);
	$mesVariables = "";
	$csvVariables = "NbVar;";
	while ($record = $result->fetch_assoc()) {

		$mesVariables .= $record['NEWFIELD'].",";
		$csvVariables .= $record['chVar']."-".$record['NEWFIELD'].";";
		
	}
	$mesVariables = substr($mesVariables,0,(strlen($mesVariables) - 1));
	
	
	$oPhonex = new phonex;
	
    

	//Est-ce le moment de générer les différents ID
	
	/*
	
	$majIDPREC=false;
	$reqSelect = "SELECT DATEEXPORT FROM `params` ";
	$result = ExecRequete($reqSelect, $maConnection);
	$record = $result->fetch_assoc();

	$dateLimite = (substr($record['DATEEXPORT'],0,4)+1)."-05-01";
	$dateJour=date("Y-m-d");
	
	if($dateJour > $dateLimite)
		$majIDPREC = true; //OK
	 else
		$majIDPREC = false;
	
	

	if($majIDPREC ){
		//Sauvegarde des identifiants
		$reqUpdate= "UPDATE `enfants` SET `IDPREC` = `ID` WHERE `enfants`.`ADTENRE` > '".$debutExport."' ";
		$result = ExecRequete($reqUpdate, $maConnection);
		
		//Génération ou Maj des identifiants
		$reqSelect = "SELECT * FROM `enfants` WHERE `enfants`.`ADTENRE` >='".$debutExport."' ";
		$result = ExecRequete($reqSelect, $maConnection);
		
		$oSha = new cleID;
		
	
		while ($record = $result->fetch_assoc()) {
			$oSha->genereID($record);
			$reqUpdate = "UPDATE `enfants` SET `ID` = '".$oSha->sSHA1."' WHERE `enfants`.`IDENFANT` ='".$record['IDENFANT']."' LIMIT 1 ; ";
			$res = ExecRequete($reqUpdate, $maConnection);
		}

			
		//On renseigne la table params
		$reqUpdate="UPDATE `params` SET `DATEEXPORT` = '".$dateJour."' ";
		$result = ExecRequete($reqUpdate, $maConnection);
		
		
	}else{
		//On génère les identifiants pour chaque enfant qui ne serait pas déjà renseigné et seulement ceux-ci.
		$reqSelect = "SELECT * FROM `enfants` WHERE `enfants`.`ID` IS NULL ";
		$result = ExecRequete($reqSelect, $maConnection);
		
		$oSha = new cleID;
		
	
		while ($record = $result->fetch_assoc()) {
			$oSha->genereID($record);
			$reqUpdate = "UPDATE `enfants` SET `ID` = '".$oSha->sSHA1."', `IDPREC` = '".$oSha->sSHA1."' WHERE `enfants`.`IDENFANT` ='".$record['IDENFANT']."' LIMIT 1 ; ";
			$res = ExecRequete($reqUpdate, $maConnection);
		}
	
	
	}
	
	*/
	
	//Création du fichier XML des enregistrement des 16 mois précédents
		
	
	$csvstr = $csvVariables."\n";
	

	$reqSelect = "SELECT ".$mesVariables." from DONNEES ";
	/*
	$reqSelect .= " left join enfants using(IDENFANT) ";
	$reqSelect .= " WHERE  `donnees`.`datemodif` > '".$debutExport."'  AND `donnees`.`datemodif` < '".$finExport. "' ";
	*/
	
	
	
	$result = ExecRequete($reqSelect,  $maConnection);

	while ($record = $result->fetch_assoc()) {
		$csvstr .=export_elementcsv($record, $csvstr);
	}
	
	
	echo $csvstr;

	
	// Sauvegarde du ficher csv
	$csvfile="./export.csv";
	$fp = fopen($csvfile, 'w'); 
	fputs($fp, $csvstr);
	fclose($fp); 	
	

	
?>
