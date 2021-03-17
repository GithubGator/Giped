<?php
	/******************************************************************************************************/
	/*              Ce script génère le fichier SQL pour la création de la table DONNEES                                                      */
	/*              à partir de la table NOMENCLATURE et des tables de référence associées                                              */
	/*             L'idée est de récupérer ce fichier pour le coller dans phpmyadmin et generer la table DONNEE         */
	/******************************************************************************************************/
	require_once("../_dbclass.php"); 
	require_once("../_connect.php"); // ParamÂtres de connection et... connection
	require_once("../_fonctions.php"); 


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
	$reqSelectOne="";
	$reqInsert="";
	$bufID="";
	

/*

	$fichier = "./oneddb.sql";
	$tabfich=file($fichier);
	for( $i = 0 ; $i < count($tabfich) ; $i++ )
	{
		$pos = strpos($tabfich[$i],"ref_");
		if($pos > 0){
		for($j=$pos+3; $j<$pos+20;$j++){
			$tabfich[$i][$j] = strtoupper($tabfich[$i][$j]);
			if($tabfich[$i][$j] =='(') break;
		}

		}
		
		echo "<pre>";
		echo $tabfich[$i] ;
		echo "</pre>";	
	} 

	exit();
*/
	$i=0;
	

	$varNomenclature ="";
	echo "
		DROP TABLE IF EXISTS `DONNEES`;
		CREATE TABLE IF NOT EXISTS `DONNEES` (
		`ID` bigint(20) NOT NULL auto_increment,";

	$reqSelect = "SELECT * FROM `NOMENCLATURE` WHERE `NEWFIELD` <>''   ORDER BY `NEWFIELD`";
	//$reqSelect = "SELECT ID, id_donnee FROM `donnees` ORDER BY ID ";
	$result = ExecRequete($reqSelect, $maConnection);
	
		
	while ($record = $result->fetch_assoc()) {
		$i++;
	
		//echo $i."->". $record['NEWFIELD']."<br>";
		$varNomenclature .=
	'var $'.$record['NEWFIELD'].';
	';
		
		if($record['Type'] !='date'){
		$nomTable = "ref_".$record['NEWFIELD'];
		//echo $nomTable;
		$declarationClass = 'class '.$nomTable.' extends decret ';
		$declarationClass .=' 	
		{
			var $id;
			var $libelle;
		}';
		/*
		echo "<pre>";
		echo $declarationClass;
		echo "</pre>";
		*/
 
			
			if(strpos("ref_NUMANONYM;ref_NUMANONYMANT,ref_MNAIS,ref_ANAIS;ref_NBPER;ref_NBFRAT;ref_ANSMERE; ref_ANSPERE;ref_DATDCMERE; ref_DATDCPERE; ref_AUTREDA; ref_AUTREDJ; ref_DATDECMIN; ref_ANSA1; ref_ANSA2;ref_NBCHGLIEU;ref_NBENF",$nomTable) === false){
		
				//$req ="SELECT * FROM $nomTable" ;		
				//$res = ExecRequete($req, $maConnection);
				/*
				// Maj Type enum
				$type = "enum(";
				while ($ligne = $res->fetch_assoc()) {
					$type.="'".$ligne['id']."',";
				
				}
				$type = substr($type,0,-1);
				$type.= ")";
				echo $record['Type']."->".$type;
				$req ="UPDATE nomenclature SET Type =\"".$type. "\" WHERE NEWFIELD =\"".$record['NEWFIELD']."\"";		
				//echo $req;
				$res = ExecRequete($req, $maConnection);
				// Fin Maj Type enum
				*/
				//$req= " ALTER TABLE `".$nomTable."` COMMENT = '".str_replace("'","\'",$record['Lib'])."'"; 
				//$res = ExecRequete($req, $maConnection);
				echo  "`".$record['NEWFIELD']."`". str_replace("m('","m('','", $record['Type'])." NOT NULL  COMMENT '".str_replace("'","\'",$record['Lib'])."', </br>";
			
			} else {
				echo  "`".$record['NEWFIELD']."` ". $record['Type']." NOT NULL  COMMENT '".str_replace("'","\'",$record['Lib'])."', </br>";

			}
		
		}else{
			echo  "`".$record['NEWFIELD']."` date NOT NULL  COMMENT '".str_replace("'","\'",$record['Lib'])."', </br>";

		}
		
	}
	echo " PRIMARY KEY  (`ID`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Remontée des données des départements' AUTO_INCREMENT=1 ";

	/* Champ de nomenclature
	echo "<pre>";
	echo $varNomenclature;
	echo "</pre>";
	*/

	
	
?>
