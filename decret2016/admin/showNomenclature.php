<?php
	/******************************************************************************************************/
	/* Ce script génère un tableau CALC à partir de la table NOMENCLATURE et des tables de référence associées    */
	/*                                                                                                                                                                                                    */
	/******************************************************************************************************/
	
	require_once("../_dbclass.php"); 
	require_once("../_connect.php"); // Paramètres de connection et... connection
	require_once("../_fonctions.php"); 


	header("Content-type: text/html; charset=utf-8");
	setlocale(LC_ALL, 'fr_FR.utf8');
	//error_reporting(E_ERROR | E_PARSE);
	error_reporting(E_ALL);
	import_request_variables("GP");
	//
	
	// Période concernée
	$debutExport = date("Y",strtotime("-1 year"))."-01-01"; 
	$finExport =  date("Y")."-05-01"; 

	$reqUpdate="";
	$reqSelect="";
	$reqSelectOne="";
	$reqInsert="";
	$bufID="";
	

	$i=0;
	
	$xsd = '';

	//$order = "numDecret"; //pour pointage après génération du schéma
	$order = "NewField";
	$reqSelect = "SELECT * FROM `NOMENCLATURE` WHERE `NewField` <>''  ";
	if(isset($order)){
		$reqSelect .= " ORDER BY ".$order;
		//echo $order; exit();
		
	} else {
		$reqSelect .= " ORDER BY `NewField`";
	}
	//$reqSelect = "SELECT ID, id_donnee FROM `donnees` ORDER BY ID ";
	
	$result = ExecRequete($reqSelect, $maConnection);
	
		
	while ($record = $result->fetch_assoc()) {
		$i++;
	
		//echo $i."->". $record['NewField']."<br>";
		
		// tous les éléments sont facultatifs sauf  NUMANONYM qui doit exister, de plus chaque élément ne peut avoir qu'une seule instance dans la sequence
		
		$xsd .= $record['numVar'].";".$record['Alinea'].";".$record['NewField'].";;".trim($record['Lib']) .";\n" ;
		
		if($record['Type'] !='date'){
			$nomTable = "ref_".$record['NewField'];
			
			if(strpos("ref_NUMANONYM;ref_NUMANONYMANT,ref_MNAIS,ref_ANAIS;ref_NBPER;ref_NBFRAT;ref_ANSMERE; ref_ANSPERE;ref_DATDCMERE; ref_DATDCPERE; ref_AUTREDA; ref_AUTREDJ; ref_DATDECMIN; ref_ANSA1; ref_ANSA2;ref_NBCHGLIEU;ref_NBENF",$nomTable) === false){
							
				$req ="SELECT * FROM $nomTable" ;		
				$res = ExecRequete($req, $maConnection);

				while ($ligne = $res->fetch_assoc()) {
					$xsd .= ";;;  ".$ligne['id'].";".$ligne['libelle'].";\n";
				}
		
			
			} else {
				//echo  "`".$record['NewField']."` ". $record['Type']." NOT NULL  COMMENT '".str_replace("'","\'",$record['Lib'])."', </br>";
		
			}
	
		}
		
	}
	
	echo "<pre>";
	echo $xsd;
	echo "</pre>";
	
	
	// Sauvegarde du schéma
	$xsdfile="./decret2016.csv";
	$fp = fopen($xsdfile, 'w'); 
	fputs($fp, $xsd);
	fclose($fp); 	
	
?>
