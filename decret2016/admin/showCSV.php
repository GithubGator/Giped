<?php
	/******************************************************************************************************/
	/* Ce script génère le fichier csv à partir de la table NOMENCLATURE et des tables de référence associées    */
	/*             L'idée est de récupérer ce fichier comme gabarit pour le guide d'aide à la saisie                                      */
	/******************************************************************************************************/
	
	require_once("../_dbclass.php"); 
	require_once("../_connect.php"); // ParamÂtres de connection et... connection
	require_once("../_fonctions.php"); 

546849370
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
	
	$order = "numDecret"; //pour pointage après génération du schéma
	
	$reqSelect = "SELECT * FROM `NOMENCLATURE` WHERE `NEWFIELD` <>''  ";
	if(isset($order)){
		$reqSelect .= " ORDER BY ".$order;
		//echo $order; exit();
		
	} else {
		$reqSelect .= " ORDER BY `NEWFIELD`";
	}
	//$reqSelect = "SELECT ID, id_donnee FROM `donnees` ORDER BY ID ";
	
	$result = ExecRequete($reqSelect, $maConnection);
	
	$csv="";	
	while ($record = $result->fetch_assoc()) {
		$i++;
	
		//echo $i."->". $record['NEWFIELD']."<br>";
		
		// tous les éléments sont facultatifs sauf  NUMANONYM qui doit exister, de plus chaque élément ne peut avoir qu'une seule instance dans la sequence
		
		$csv .= "\n".$i.";Variable ".$i.";".$record['NEWFIELD'].";".str_replace("’","'",utf8_decode($record['Lib']));
		
		if($record['Type'] !='date'){
		$nomTable = "ref_".$record['NEWFIELD'];
		
		if(strpos("ref_NUMANONYM;ref_NUMANONYMANT,ref_MNAIS,ref_ANAIS;ref_NBPER;ref_NBFRAT;ref_ANSMERE; ref_ANSPERE;ref_DATDCMERE; ref_DATDCPERE; ref_AUTREDA; ref_AUTREDJ; ref_DATDECMIN; ref_ANSA1; ref_ANSA2;ref_NBCHGLIEU;ref_NBENF",$nomTable) === false){
						
			$req ="SELECT * FROM $nomTable" ;		
			$res = ExecRequete($req, $maConnection);
			$nbLib=0;
			while ($ligne = $res->fetch_assoc()) {
				if($nbLib==0){
					$csv .= ";".$ligne['id'].":".utf8_decode($ligne['libelle'])."\n";
					$nbLib++;
				}else{
					$csv .= ";;;;".$ligne['id'].":".utf8_decode($ligne['libelle'])."\n";
				}
			
			}
		
		} else {
			//echo  "`".$record['NEWFIELD']."` ". $record['Type']." NOT NULL  COMMENT '".str_replace("'","\'",$record['Lib'])."', </br>";
			
			switch ($record['NEWFIELD']) {
				case 'NUMANONYM':
				case 'NUMANONYMANT' :
					$xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                   <xs:pattern value=\"[0-9a-fA-F]{40}\" />\n";	
					break;
				case 'ANAIS' :
				case 'ANSA1' :
				case 'ANSA2':
				case 'ANSMERE' :
				case 'ANSPERE' :
				        $xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                   <xs:pattern value=\"(9999)|()|(0000)|(19)[0-9]{2}|(20)[0-9]{2}\" />\n";	
					break;
				case 'MNAIS':
				case 'NBFRAT' :
				case 'NBPER':
				case 'NBENF':
				case 'NBCHGLIEU':
					$xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                   <xs:pattern value=\"()|[0-9]{1,2}\" />\n";	
					break;
				case 'AUTREDA' :
				case 'AUTREDJ':
					$xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                   <xs:maxLength value=\"40\" />\n";
					break;
				case 'DATDCMERE' :
				case 'DATDCPERE' :
				case 'DATDECMIN':
					$xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                   <xs:pattern value=\"()|(0000-00)|((19)[0-9]{2}|(20)[0-9]{2})-((99)|(0)[1-9]{1}|(1)[0-2]{1})\" />\n";	
					break;
				case 'AUTRE' :
					$xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                    <xs:enumeration value=\"\" />\n";
					$xsd .="                    <xs:enumeration value=\"1\" /><!-- OUI -->\n";
					$xsd .="                    <xs:enumeration value=\"2\" /><!-- NON -->\n";
					$xsd .="                    <xs:enumeration value=\"9\" /><!-- NE SAIT PAS -->\n";
					break;
				default :
					$xsd .="                  <xs:restriction base=\"xs:string\">\n";
					$xsd .="                   <xs:pattern value=\"[0-9]{4}\" /><! -- A modifier  -->\n";	
					echo  "`".$record['NEWFIELD']."` ". $record['Type']." NOT NULL  COMMENT '".str_replace("'","\'",$record['Lib'])."', </br>";
					break;
			}
			
			


		}
			$xsd .="                  </xs:restriction>\n";
			$xsd .="                </xs:simpleType>\n";
			$xsd .="              </xs:element>\n";
	
		
		
		}else {
			//dates
					
		}
		
	}
	
	
	/* Champ de nomenclature
	echo "<pre>";
	echo $varNomenclature;
	echo "</pre>";
	*/

	echo $csv;
	
	// Sauvegarde du schéma
	$csvfile="./decret2016.csv";
	$fp = fopen($csvfile, 'w'); 
	fputs($fp, $csv);
	fclose($fp); 	
	
?>
