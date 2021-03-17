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
	error_reporting(E_ALL);
	import_request_variables("GP","e_");

	
	$error = array();
	// Objet dom XML pour valider le schéma
	$dom = new DomDocument();
	
	$folder = "/home/imports_dept";
	
	if(!isset ($e_file)){
	
		//$folder = "./imports";
		
		$dossier = opendir($folder);
		while ($Fichier = readdir($dossier)) {
		  if ($Fichier != "." && $Fichier != "..") {
		    $nomFichier = $folder."/".$Fichier;
		    //echo $nomFichier."<BR>";
		    echo "<a href='import.php?file=".$Fichier."'>".$Fichier."</a><BR>";
		  }
		}
		closedir($dossier);
	
		exit();
	
	}
	
	// nom du fichier
	//$nomFic="ONED-035-20120702_2012.XML";

	// Lecture du ficher xml
	//$xmlfile="./imports/".$nomFic;
	
	$xmlfile=$folder."/".$e_file;
	
	// echo "Chargement du fichier XML";
	$i=0;
	$xml = simplexml_load_file($xmlfile);
	
	if (!$xml) {
		echo "Erreur lors du chargement du XML\n";
		foreach(libxml_get_errors() as $error) {
			echo "\t", $error->message;
		}
	}
	//print_r($xml); 
	$sql="TRUNCATE TABLE `DONNEES_TMP`";
	$result  = ExecRequete($sql, $maConnection);
	
	//$cleSecreteONED= "YIGGKHL";

	// Insertion base de donnée
	$i=0;
	foreach ($xml->DONNEE as $Element) {
		$i++;
		//echo $i." ".$Element->ID. "<br /> ";
		$sql = "";
		$cols="";
		$values = "";
		foreach($Element as $cle=>$val){
			$cols.=$cle.",";
			if($cle =="NUMANONYM" || $cle =="NUMANONYMANT" ){
				$newVal = substr(hash('sha1',substr($val,0,20).$cleSecreteONED),0,20).substr(hash('sha1',substr($val,20,20).$cleSecreteONED),0,20);
				//echo "cle ".$val."----".$newVal."<br />";
				$values.="'".$newVal."',";
			
			}else{
				$values.="'".addslashes($val)."',";
			}
		}
		$cols = substr($cols,0,(strlen($cols) - 1));
		$values = substr($values,0,(strlen($values) - 1));
		/*
		echo $cols;
		echo "<br/>";
		echo $values;
		echo "<br/>";
		*/
		
		if($i >100000){ // n'est plus utile
			// pour identifier les doublons on commence avec 0 pour avoir le Premier pas
			echo $i." ".$Element->ID. "-> enregistrement OK <br /> ";
			$sql = "insert into DONNEES_TMP (".$cols.") values (".$values.")";
		}else{
			$sql = "insert IGNORE into DONNEES_TMP (".$cols.") values (".$values.")";
		}
		
		
		//echo $sql ."<br />";
		$result  = ExecRequete($sql, $maConnection);
	}
	//exit();
	
	echo "Nombre de lignes d&eacute;tect&eacute;es : ".$i."<br /> ";
	$sql = "UPDATE DONNEES_TMP SET CLEFUNIQUE = CONCAT(ANSA1,ANSA2,CODEV,LIENA1,LIENA2,NUMANONYM,NUMANONYMANT,SEXA1,SEXA2,SEXE,DATDEBPLAC,DATFINPLAC,DATDECPE,DATDEBINTER,DATFININTER,DATSIGN,DATJE,DATAVIS,DATDECAP,DECISION,TYPINTERDOM,NATNOUVDECPE,NOTIFEVAL,NATDECASSED,NATPDECADM)";
	
	$result  = ExecRequete($sql, $maConnection);
	
	$sql = "Select ID from DONNEES_TMP WHERE 1 GROUP BY CLEFUNIQUE";
	$result  = ExecRequete($sql, $maConnection);
	
	echo "Nombre de lignes diff&eacute;rentes : ".$result->num_rows."<br /> ";


	// fichier csv
	$separateur=";";
	$csvstr="";
	
	// création de la structure du fichier CSV
	$sql = "SELECT `NEWFIELD`  FROM `nomenclature` WHERE `KEEP` LIKE '1' OR `KEEP` LIKE '2'  ORDER BY `NEWFIELD` ";
	//echo $sql ."<br />";
	$result  = ExecRequete($sql, $maConnection);
	
	$tabCol = array();
	$mesVariables = "";
	while ($record = $result->fetch_assoc()) {
		$mesVariables .= $record['NEWFIELD'].",<br />";
		$tabCol[$record['NEWFIELD']]="NULL";	
		$csvstr .= $record['NEWFIELD'].$separateur;
	}
	$csvstr.= "\n";
	
	//echo $mesVariables;
	//print_r($tabCol);
		
	foreach ($xml->DONNEE as $Element) {

		foreach($Element as $cle=>$val){
			
			if($cle =="NUMANONYM" || $cle =="NUMANONYMANT" ){
				$newVal = substr(hash('sha1',substr((string)$val,0,20).$cleSecreteONED),0,20).substr(hash('sha1',substr((string)$val,20,20).$cleSecreteONED),0,20);
				//echo "cle ".$val."----".$newVal."<br />";
				$tabCol[$cle]= $newVal;    // $val est un objet il faut un cast
				
			}else{
				//echo $cle ."--->".$val."<br />";
				$tabCol[$cle]= (string)$val;    // $val est un objet il faut un cast
				// traitement des dates au format jj/mm/AAAA
				if(preg_match( '`^\d{2}/\d{2}/\d{4}$`' , $tabCol[$cle] )){
					$parts = explode("/", $tabCol[$cle] );
					
					$tabCol[$cle] = $parts[2]."-".$parts[1]."-".$parts[0];	
					echo "date : ".(string)$val."----".$tabCol[$cle]."<br />";
				}
				
			}
		}
	
		foreach($tabCol as $cle=>$val){
			if ($val != "NULL")
				$csvstr .= $val.$separateur;
			else
				$csvstr .= $separateur;
			// on initialise
			$tabCol[$cle]="NULL";
		}
		$csvstr.= "\n";
		/*
		print_r($tabCol);
		echo "<br />***************************************************<br />";
		*/
	}
	
	
	// Sauvegarde du ficher xml
	$dateJour=date("Y-m-d");
	//$csvfile="./".$Fichier."-".$dateJour.".csv";
	$csvfile= substr($e_file,0,-3)."csv";
	echo $csvfile;
	$fp = fopen($csvfile, 'w'); 
	fputs($fp, $csvstr);
	fclose($fp); 	
	
	
?>
