<?php

	require_once("../_dbclass.php"); 
	require_once("../_connect.php"); // ParamÂtres de connection et... connection
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
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
	
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title> Variables du d&eacute;cret</title> 
	<!-- appel des css -->
	<style type="text/css">
	<!--
	
	.titre {
	  font-family: Verdana, Helvetica, Arial, sans-serif;
	  font-size: 14px; 
	  background-color: #ECE9D8;
	  width:840px;
	}
	
	table {
	  border-collapse: collapse;
	  font-family: Verdana, Helvetica, Arial, sans-serif;
	  font-size: 12px; 
 
	}
	
	 table td {
	  border: solid black 1px;
	  padding-left: 10px;
	  
	}	  
	table th {
	  background: #AACCEE;
	  border: 1px solid #565248;
	  color: #000; 
	}
	tr.pair {
		background-color: #ECE9D8;
	}

	tr.impair {
		background-color: #FFFFE1;
	}
	-->
	</style>
</head>


	<body>


	<form method="post" action="showbase.php">
						
	<center>
	
	<br />
	
	<label for="order" accesskey="T" >Trier par</label>
	<select name="order" id="order" title="Trier par...">
			<option value="NEWFIELD">Ordre Alphab&eacute;tique</option>
			<option value="ALINEA">Ordre du d&eacute;cret</option>

	</select>

	<input type=submit name="Valider" id="Valider" value="Valider">&nbsp;&nbsp;&nbsp;&nbsp;
	<hr />
	
	<br />
	 
	
	</center>

	</form>
	


<?php
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
	


	$reqSelect = "SELECT * FROM `nomenclature` WHERE `NEWFIELD` <>'' AND export = 1 AND `NEWFIELD` <>'SUP' AND (KEEP =1 OR KEEP = 2)";
	if(isset($order)){
		$reqSelect .= " ORDER BY ".$order;
		//echo $order; exit();
		
	} else {
		$reqSelect .= " ORDER BY `NEWFIELD`";
	}
	//$reqSelect = "SELECT ID, id_donnee FROM `donnees` ORDER BY ID ";
	
	$result = ExecRequete($reqSelect, $maConnection);
	
		
	while ($record = $result->fetch_assoc()) {
		$i++;
	
		//echo $i."->". $record['NEWFIELD']."<br>";
		
		if($record['Type'] !='date'){
		$nomTable = "ref_".$record['NEWFIELD'];
		//echo $nomTable;
		//echo "<div class=\"titre\">".$record['ALINEA']. " - ".$record['NEWFIELD']." : ". $record['Lib']. "</div><br/>";
		
		
		if(strpos("ref_NUMANONYM;ref_NUMANONYMANT,ref_MNAIS,ref_ANAIS;ref_NBPER;ref_NBFRAT;ref_ANSMERE; ref_ANSPERE;ref_DATDCMERE; ref_DATDCPERE; ref_AUTREDA; ref_AUTREDJ; ref_DATDECMIN; ref_ANSA1; ref_ANSA2",$nomTable) === false){
			
			$req ="SELECT * FROM $nomTable" ;		
			$res = ExecRequete($req, $maConnection);

			while ($ligne = $res->fetch_assoc()) {

				echo $record['NEWFIELD'].";".$ligne['id'].";".$ligne['libelle'].";".$record['Lib'].";";
				echo "<br />";
			
			}

			//$req= " ALTER TABLE `".$nomTable."` COMMENT = '".str_replace("'","\'",$record['Lib'])."'"; 
			//$res = ExecRequete($req, $maConnection);
			//echo  "`".$record['NEWFIELD']."`". str_replace("m('","m('','", $record['Type'])." NOT NULL  COMMENT '".str_replace("'","\'",$record['Lib'])."', </br>";
		
		} else {
			echo  $record['NEWFIELD'].";". $record['Type']." ;-; ".str_replace("'","\'",$record['Lib']).";";
			echo "<br />";

		}
		
		
		
		}else {
		//echo "<div class=\"titre\">".$record['ALINEA']. " - ".$record['NEWFIELD']." : ". $record['Lib']. "</div><br/>";
		
		}
	
	}
	
	
	/* Champ de nomenclature
	echo "<pre>";
	echo $varNomenclature;
	echo "</pre>";
	*/

	
	
?>
</body>
</html>
